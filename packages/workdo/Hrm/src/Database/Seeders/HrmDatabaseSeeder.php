<?php

namespace Workdo\Hrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class HrmDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(EmailTemplateTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(RoleTableSeeder::class);
        $this->call(NotificationsTableSeeder::class);
        if (module_is_active('CustomField')) {
            $this->call(CustomFieldListTableSeeder::class);
        }
        if (module_is_active('AIAssistant')) {
            $this->call(AIAssistantTemplateListTableSeeder::class);
        }
        if (module_is_active('LandingPage')) {
            $this->call(MarketPlaceSeederTableSeeder::class);
        };
    }
}
