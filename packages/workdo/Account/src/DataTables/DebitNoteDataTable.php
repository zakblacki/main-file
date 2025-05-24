<?php

namespace Workdo\Account\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\DebitNote;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DebitNoteDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumn = [ 'bill','date', 'vendor', 'amount','status','description'];
        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('bill', function (CustomerDebitNotes $customdebitNote) {
                if (\Laratrust::hasPermission('bill show')) {
                    $url = route('bill.show', \Crypt::encrypt($customdebitNote->bill));
                    return '<a href="' . $url . '" class="btn btn-outline-primary">' . Bill::billNumberFormat($customdebitNote->bill_number->bill_id) . '</a>';
                } else {
                    return ' <a href="#" class="btn btn-outline-primary">' . Bill::billNumberFormat($customdebitNote->bill_number->bill_id) . '</a>';
                }
            })
            ->editColumn('date', function (CustomerDebitNotes $customdebitNote) {
                return company_date_formate($customdebitNote->date);
            })

            ->editColumn('vendor', function (CustomerDebitNotes $customdebitNote) {
                return !empty($customdebitNote->custom_vendor) ? $customdebitNote->custom_vendor->name : '';
            })

            ->editColumn('description', function (CustomerDebitNotes $customdebitNote) {
                return !empty($customdebitNote->description) ? $customdebitNote->description : '--';
            })

            ->editColumn('amount', function (CustomerDebitNotes $customdebitNote) {
                return currency_format_with_sym($customdebitNote->amount);
            })
            ->editColumn('status', function (CustomerDebitNotes $customdebitNote) {
                if ($customdebitNote->status == 0) {
                    $class = 'bg-primary';
                } elseif ($customdebitNote->status == 1) {
                    $class = 'bg-info';
                } elseif ($customdebitNote->status == 2) {
                    $class = 'bg-secondary';
                }
                return '<span class="badge ' . $class . ' p-2 px-3">' . CustomerDebitNotes::$statues[$customdebitNote->status] . '</span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                if (stripos('Pending', $keyword) !== false) {
                    $query->where('customer_debit_notes.status', 0);
                } elseif (stripos('Partially Used', $keyword) !== false) {
                    $query->orWhere('customer_debit_notes.status', 1);
                } elseif (stripos('Fully Used', $keyword) !== false) {
                    $query->orWhere('customer_debit_notes.status', 2);
                }
            });

            $dataTable->filterColumn('vendor', function ($query, $keyword) {
                $query->whereHas('custom_vendor', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            });


        if (\Laratrust::hasPermission('debitnote edit') || \Laratrust::hasPermission('debitnote delete')) {
            $dataTable->addColumn('action', function (CustomerDebitNotes $customdebitNote) {

                return view('account::customerDebitNote.action', compact('customdebitNote'));
            });
            $rawColumn[] = 'action';
        }
        return $dataTable->rawColumns($rawColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CustomerDebitNotes $model)
    {

        $customdebitNotes = $model->with('custom_vendor')->select('customer_debit_notes.*')
            ->leftJoin('bills', 'customer_debit_notes.bill', '=', 'bills.id')
            ->where('bills.workspace', getActiveWorkSpace());

        return $customdebitNotes;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('debitnote-table')
            ->columns($this->getColumns())
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
                    return new bootstrap.Popover(tooltipTriggerEl);
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
            Column::make('bill')->title(__('Bill')),
            Column::make('vendor')->title(__('Vendor')),
            Column::make('date')->title(__('Date')),
            Column::make('amount')->title(__('Amount')),
            Column::make('description')->title(__('Description')),
            Column::make('status')->title(__('Status')),
        ];


        if (
            \Laratrust::hasPermission('debitnote edit') ||
            \Laratrust::hasPermission('debitnote delete')
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
     *
     *
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Debit_Notes_' . date('YmdHis');
    }
}
