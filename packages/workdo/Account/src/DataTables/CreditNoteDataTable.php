<?php

namespace Workdo\Account\DataTables;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\CustomerCreditNotes;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class CreditNoteDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = ['invoice', 'date', 'customer', 'amount','status','description'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('invoice', function (CustomerCreditNotes $customcreditNote) {
                if (\Laratrust::hasPermission('invoice show')) {
                    $url = route('invoice.show', \Crypt::encrypt($customcreditNote->invoice));
                    return '<a href="' . $url . '" class="btn btn-outline-primary">' . Invoice::invoiceNumberFormat($customcreditNote->invoices->invoice_id) . '</a>';
                } else {
                    return ' <a href="#" class="btn btn-outline-primary">' . Invoice::invoiceNumberFormat($customcreditNote->invoices->invoice_id) . '</a>';
                }
            })
            ->editColumn('date', function (CustomerCreditNotes $customcreditNote) {
                return company_date_formate($customcreditNote->date);
            })

            ->editColumn('description', function (CustomerCreditNotes $customcreditNote) {
                return isset($customcreditNote->description) ? $customcreditNote->description : '--';
            })

            ->editColumn('customer', function (CustomerCreditNotes $customcreditNote) {
                return !empty($customcreditNote->custom_customer) ? $customcreditNote->custom_customer->name : '--';
            })

            ->editColumn('amount', function (CustomerCreditNotes $customcreditNote) {
                return currency_format_with_sym($customcreditNote->amount);
            })
            ->editColumn('status', function (CustomerCreditNotes $customcreditNote) {
                if ($customcreditNote->status == 0) {
                    $class = 'bg-primary';
                } elseif ($customcreditNote->status == 1) {
                    $class = 'bg-info';
                } elseif ($customcreditNote->status == 2) {
                    $class = 'bg-secondary';
                }
                return '<span class="badge ' . $class . ' p-2 px-3">' . CustomerCreditNotes::$statues[$customcreditNote->status] . '</span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                if (stripos('Pending', $keyword) !== false) {
                    $query->where('customer_credit_notes.status', 0);
                } elseif (stripos('Partially Used', $keyword) !== false) {
                    $query->orWhere('customer_credit_notes.status', 1);
                } elseif (stripos('Fully Used', $keyword) !== false) {
                    $query->orWhere('customer_credit_notes.status', 2);
                }
            });

            $dataTable->filterColumn('customer', function ($query, $keyword) {
                $query->whereHas('custom_customer', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            });


        if (\Laratrust::hasPermission('creditnote edit') || \Laratrust::hasPermission('creditnote delete')) {
            $dataTable->addColumn('action', function (CustomerCreditNotes $customcreditNote) {

                return view('account::customerCreditNote.action', compact('customcreditNote'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CustomerCreditNotes $model)
    {
        $customcreditNotes = $model->whereHas('invoices', function ($query) {
            $query->where('workspace', getActiveWorkSpace());
        })->with(['custom_customer','invoices']);
        return $customcreditNotes;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('creditnote-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_" . __('Entries Per Page'),
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __('Showing _START_ to _END_ of _TOTAL_ entries')
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }');

        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary dropdown-toggle',
            'text' => '<i class="ti ti-download me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Export"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print me-2"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            [
                'extend' => 'reset',
                'className' => 'btn btn-light-danger',
            ],
            [
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "dom" =>  "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-2'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {

        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('invoice')->title(__('Invoice'))->searchable(false),
            Column::make('customer')->title(__('Customer')),
            Column::make('date')->title(__('Date')),
            Column::make('amount')->title(__('Amount')),
            Column::make('description')->title(__('Description')),
            Column::make('status')->title(__('Status')),
        ];


        if (
            \Laratrust::hasPermission('creditnote edit') ||
            \Laratrust::hasPermission('creditnote delete')
        ) {

            $action = [
                Column::computed('action')
                    ->exportable(false)
                    ->printable(false)
                    ->width(60)

            ];

            $column = array_merge($column, $action);
        }

        return $column;
    }


    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Credit_Notes_' . date('YmdHis');

    }
}
