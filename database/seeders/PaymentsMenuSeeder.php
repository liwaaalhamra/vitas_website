<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;

/**
 * Adds the read-only "Payments" item to the Voyager admin menu.
 * Idempotent — safe to run on any environment:  php artisan db:seed --class=PaymentsMenuSeeder
 */
class PaymentsMenuSeeder extends Seeder
{
    public function run()
    {
        $menu = Menu::where('name', 'admin')->firstOrFail();

        $item = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'url'     => 'admin/payments',
        ]);

        if (!$item->exists) {
            $item->fill([
                'title'      => 'Payments',
                'target'     => '_self',
                'icon_class' => 'voyager-credit-cards',
                'color'      => null,
                'parent_id'  => null,
                'route'      => null,
                'order'      => 43,
            ])->save();
        }
    }
}
