<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresi hardening akses: sebelumnya SELURUH route /admin/*, /print/*, dan
 * /export/* di bakso-report hanya dilindungi middleware 'auth' — tidak ada
 * 'can:xxx' sama sekali, dan tidak ada satupun authorize() di Livewire
 * component-nya. Artinya siapa pun yang login (Kasir, Kitchen, siapa saja)
 * bisa membuka /admin/users, /admin/roles, /admin/settings, dan laporan
 * keuangan langsung lewat URL. Test ini memastikan setiap route benar-benar
 * menolak (403) user yang tidak punya permission terkait, dan menerima
 * (bukan 403) user yang punya permission tersebut.
 */
class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}> label => [routeName, permission]
     */
    public static function protectedAdminRoutes(): array
    {
        return [
            'shifts'             => ['admin.shifts.index', 'view_own_shifts'],
            'returns'            => ['admin.returns', 'view_returns'],
            'report transactions'=> ['admin.reports.transactions', 'view_transactions'],
            'report sales'       => ['admin.reports.sales', 'view_sales_reports'],
            'report products'    => ['admin.reports.products', 'view_sales_reports'],
            'report shifts'      => ['admin.reports.shifts', 'view_all_shifts'],
            'report inventory'   => ['admin.reports.inventory', 'view_inventory_reports'],
            'users'              => ['admin.users.index', 'view_users'],
            'roles'              => ['admin.roles.index', 'manage_roles'],
            'settings'           => ['admin.settings.index', 'manage_settings'],
        ];
    }

    private function grantPermission(User $user, string $permission): void
    {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
    }

    #[DataProvider('protectedAdminRoutes')]
    public function test_admin_route_rejects_user_without_permission(string $routeName, string $permission): void
    {
        $user = User::factory()->create(['username' => 'noperm-' . uniqid()]);

        $this->actingAs($user)->get(route($routeName))->assertForbidden();
    }

    #[DataProvider('protectedAdminRoutes')]
    public function test_admin_route_allows_user_with_permission(string $routeName, string $permission): void
    {
        $user = User::factory()->create(['username' => 'hasperm-' . uniqid()]);
        $this->grantPermission($user, $permission);

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertStatus(200);
    }

    public function test_product_detail_route_requires_view_products_permission(): void
    {
        $category = Category::create(['name' => 'Menu', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Bakso', 'sku' => 'SKU-ACC-' . uniqid(),
            'price' => 10000, 'is_active' => true, 'track_stock' => true, 'stock' => 10,
        ]);

        $user = User::factory()->create(['username' => 'noperm-prod']);
        $this->actingAs($user)->get(route('admin.products.show', $product))->assertForbidden();

        $this->grantPermission($user, 'view_products');
        $this->actingAs($user)->get(route('admin.products.show', $product))->assertStatus(200);
    }

    public function test_export_and_print_routes_require_permission_not_just_auth(): void
    {
        $user = User::factory()->create(['username' => 'noperm-exp']);

        $this->actingAs($user)->get(route('print.sales-report'))->assertForbidden();
        $this->actingAs($user)->get(route('print.inventory-report'))->assertForbidden();
        $this->actingAs($user)->get(route('export.product-sales'))->assertForbidden();
        $this->actingAs($user)->get(route('export.shifts'))->assertForbidden();
    }

    public function test_super_admin_bypasses_all_permission_checks(): void
    {
        $user = User::factory()->create(['username' => 'super-access']);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user->assignRole('Super Admin');

        foreach (self::protectedAdminRoutes() as [$routeName, $permission]) {
            $this->actingAs($user)->get(route($routeName))->assertStatus(200);
        }
    }
}
