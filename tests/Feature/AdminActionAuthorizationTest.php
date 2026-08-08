<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Modifiers;
use App\Livewire\Admin\PaymentSources;
use App\Livewire\Admin\Products;
use App\Livewire\Admin\Roles;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Users;
use App\Models\Category;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\PaymentSource;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regresi hardening kedua: HALAMAN admin sudah di-gate lewat middleware route
 * (lihat AdminAccessControlTest), tapi AKSI TULIS individual di dalam
 * component (save, delete, deactivate, activate, dst) sebelumnya tidak
 * dicek permission granularnya sendiri sama sekali — tidak ada satupun
 * authorize() di codebase ini. Artinya seorang user dengan permission
 * "view" saja (atau bahkan tanpa permission apapun, memanggil action
 * Livewire secara langsung tanpa pernah lewat halaman) tetap bisa
 * create/edit/delete. Test ini memanggil method Livewire LANGSUNG
 * (tanpa lewat UI/tombol) untuk membuktikan enforcement terjadi di server,
 * bukan mengandalkan tombol yang disembunyikan di frontend.
 */
class AdminActionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function grantPermissions(User $user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $user->givePermissionTo($permissions);
    }

    private function makeCategory(): Category
    {
        return Category::create(['name' => 'Menu', 'sort_order' => 1, 'is_active' => true]);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'category_id' => $this->makeCategory()->id, 'name' => 'Bakso Auth Test',
            'sku' => 'SKU-AUTH-' . uniqid(), 'price' => 10000, 'is_active' => true,
            'track_stock' => true, 'stock' => 20,
        ]);
    }

    // -----------------------------------------------------------------
    // Categories
    // -----------------------------------------------------------------

    public function test_categories_save_requires_create_categories_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-cat-1']);
        $this->grantPermissions($user, ['view_categories']); // tanpa create

        Livewire::actingAs($user)->test(Categories::class)
            ->set('name', 'Kategori Baru')
            ->call('save')
            ->assertForbidden();

        $this->assertFalse(Category::where('name', 'Kategori Baru')->exists());
    }

    public function test_categories_delete_requires_delete_categories_permission(): void
    {
        $category = $this->makeCategory();
        $user = User::factory()->create(['username' => 'auth-cat-2']);
        $this->grantPermissions($user, ['view_categories', 'edit_categories']); // tanpa delete

        Livewire::actingAs($user)->test(Categories::class)
            ->call('delete', $category->id)
            ->assertForbidden();

        $this->assertNotNull(Category::find($category->id));
    }

    // -----------------------------------------------------------------
    // Modifiers
    // -----------------------------------------------------------------

    public function test_modifiers_save_group_requires_create_modifiers_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-mod-1']);
        $this->grantPermissions($user, ['view_modifiers']);

        Livewire::actingAs($user)->test(Modifiers::class)
            ->set('groupName', 'Level Pedas')
            ->call('saveGroup')
            ->assertForbidden();

        $this->assertFalse(ModifierGroup::where('name', 'Level Pedas')->exists());
    }

    public function test_modifiers_delete_group_requires_delete_modifiers_permission(): void
    {
        $group = ModifierGroup::create(['name' => 'Level Pedas', 'selection_type' => 'single']);
        $user = User::factory()->create(['username' => 'auth-mod-2']);
        $this->grantPermissions($user, ['view_modifiers', 'edit_modifiers']);

        Livewire::actingAs($user)->test(Modifiers::class)
            ->call('deleteGroup', $group->id)
            ->assertForbidden();

        $this->assertNotNull(ModifierGroup::find($group->id));
    }

    public function test_modifiers_delete_modifier_requires_delete_modifiers_permission(): void
    {
        $group = ModifierGroup::create(['name' => 'Level Pedas', 'selection_type' => 'single']);
        $modifier = Modifier::create(['modifier_group_id' => $group->id, 'name' => 'Pedas', 'price_adjustment' => 0]);
        $user = User::factory()->create(['username' => 'auth-mod-3']);
        $this->grantPermissions($user, ['view_modifiers']);

        Livewire::actingAs($user)->test(Modifiers::class)
            ->call('deleteModifier', $modifier->id)
            ->assertForbidden();

        $this->assertNotNull(Modifier::find($modifier->id));
    }

    // -----------------------------------------------------------------
    // Payment Sources
    // -----------------------------------------------------------------

    public function test_payment_sources_save_requires_manage_payment_sources_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-ps-1']);

        Livewire::actingAs($user)->test(PaymentSources::class)
            ->set('name', 'QRIS')
            ->set('type', 'qris')
            ->call('save')
            ->assertForbidden();

        $this->assertFalse(PaymentSource::where('name', 'QRIS')->exists());
    }

    public function test_payment_sources_delete_requires_manage_payment_sources_permission(): void
    {
        $ps = PaymentSource::create(['name' => 'Cash', 'type' => 'cash', 'is_active' => true, 'sort_order' => 1]);
        $user = User::factory()->create(['username' => 'auth-ps-2']);

        Livewire::actingAs($user)->test(PaymentSources::class)
            ->call('delete', $ps->id)
            ->assertForbidden();

        $this->assertNotNull(PaymentSource::find($ps->id));
    }

    // -----------------------------------------------------------------
    // Products
    // -----------------------------------------------------------------

    public function test_products_save_requires_create_products_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-prod-1']);
        $this->grantPermissions($user, ['view_products']); // hanya view, TIDAK create

        $category = $this->makeCategory();

        Livewire::actingAs($user)->test(Products::class)
            ->set('category_id', $category->id)
            ->set('name', 'Produk Baru')
            ->set('sku', 'SKU-NEW-1')
            ->set('price', 15000)
            ->call('save')
            ->assertForbidden();

        $this->assertFalse(Product::where('name', 'Produk Baru')->exists());
    }

    public function test_products_delete_requires_delete_products_permission(): void
    {
        $product = $this->makeProduct();

        $manager = User::factory()->create(['username' => 'auth-prod-2']);
        $this->grantPermissions($manager, ['view_products', 'edit_products']);

        Livewire::actingAs($manager)->test(Products::class)
            ->call('delete', $product->id)
            ->assertForbidden();

        $this->assertNotNull(Product::withTrashed()->find($product->id));
    }

    public function test_products_delete_succeeds_with_delete_products_permission(): void
    {
        $product = $this->makeProduct();

        $admin = User::factory()->create(['username' => 'auth-prod-3']);
        $this->grantPermissions($admin, ['view_products', 'delete_products']);

        Livewire::actingAs($admin)->test(Products::class)
            ->call('delete', $product->id)
            ->assertOk();

        $this->assertNull(Product::find($product->id));
    }

    public function test_products_stock_adjustment_requires_adjust_stock_permission(): void
    {
        $product = $this->makeProduct();

        $user = User::factory()->create(['username' => 'auth-prod-4']);
        $this->grantPermissions($user, ['view_products']); // tanpa adjust_stock

        Livewire::actingAs($user)->test(Products::class)
            ->call('openStockModal', $product->id)
            ->call('saveStock', 'add', 5, null)
            ->assertForbidden();

        $this->assertEquals(20, $product->fresh()->stock);
    }

    // -----------------------------------------------------------------
    // Roles & Permissions
    // -----------------------------------------------------------------

    public function test_roles_save_requires_manage_roles_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-role-1']);

        Livewire::actingAs($user)->test(Roles::class)
            ->set('name', 'Supervisor')
            ->call('save')
            ->assertForbidden();

        $this->assertFalse(Role::where('name', 'Supervisor')->exists());
    }

    public function test_roles_delete_requires_manage_roles_permission(): void
    {
        $role = Role::firstOrCreate(['name' => 'Kasir 2', 'guard_name' => 'web']);
        $user = User::factory()->create(['username' => 'auth-role-2']);

        Livewire::actingAs($user)->test(Roles::class)
            ->call('delete', $role->id)
            ->assertForbidden();

        $this->assertNotNull(Role::find($role->id));
    }

    // -----------------------------------------------------------------
    // Settings
    // -----------------------------------------------------------------

    public function test_settings_save_general_requires_manage_settings_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-set-1']);

        Livewire::actingAs($user)->test(Settings::class)
            ->set('store_name', 'Toko Baru')
            ->call('saveGeneral')
            ->assertForbidden();

        $this->assertNotEquals('Toko Baru', \App\Models\Setting::get('store_name', '', 'general'));
    }

    public function test_settings_save_printer_requires_manage_printers_permission(): void
    {
        $user = User::factory()->create(['username' => 'auth-set-2']);
        $this->grantPermissions($user, ['manage_settings']); // punya manage_settings, TAPI bukan manage_printers

        Livewire::actingAs($user)->test(Settings::class)
            ->call('savePrinter')
            ->assertForbidden();
    }

    // -----------------------------------------------------------------
    // Users — deactivate (soft delete) & activate
    // -----------------------------------------------------------------

    public function test_users_deactivate_requires_delete_users_permission(): void
    {
        $target = User::factory()->create(['username' => 'target-user']);

        $staff = User::factory()->create(['username' => 'auth-users-1']);
        $this->grantPermissions($staff, ['view_users']); // tanpa delete_users

        Livewire::actingAs($staff)->test(Users::class)
            ->call('deactivate', $target->id)
            ->assertForbidden();

        $this->assertTrue(User::find($target->id)->isActive());
    }

    public function test_users_deactivate_succeeds_and_is_soft_delete_not_hard_delete(): void
    {
        $target = User::factory()->create(['username' => 'target-user-2']);

        $admin = User::factory()->create(['username' => 'auth-users-2']);
        $this->grantPermissions($admin, ['view_users', 'delete_users']);

        Livewire::actingAs($admin)->test(Users::class)
            ->call('deactivate', $target->id)
            ->assertOk();

        // Baris masih ada di DB (soft delete), bukan hard delete.
        $trashed = User::withTrashed()->find($target->id);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertFalse($trashed->isActive());

        // Tidak muncul di query normal (default scope soft delete).
        $this->assertNull(User::find($target->id));
    }

    public function test_users_activate_requires_activate_users_permission_not_just_edit_users(): void
    {
        $target = User::factory()->create(['username' => 'target-user-3']);
        $target->deactivate();

        // Staff punya edit_users (bisa ubah data user) tapi TIDAK activate_users —
        // reaktivasi harus tetap ditolak walau bisa edit data user biasa.
        $staff = User::factory()->create(['username' => 'auth-users-3']);
        $this->grantPermissions($staff, ['view_users', 'edit_users']);

        Livewire::actingAs($staff)->test(Users::class)
            ->call('activate', $target->id)
            ->assertForbidden();

        $this->assertFalse(User::withTrashed()->find($target->id)->isActive());
    }

    public function test_users_activate_succeeds_with_activate_users_permission(): void
    {
        $target = User::factory()->create(['username' => 'target-user-4']);
        $target->deactivate();

        $admin = User::factory()->create(['username' => 'auth-users-4']);
        $this->grantPermissions($admin, ['view_users', 'activate_users']);

        Livewire::actingAs($admin)->test(Users::class)
            ->call('activate', $target->id)
            ->assertOk();

        $this->assertTrue(User::find($target->id)->isActive());
    }

    public function test_users_save_create_requires_create_users_permission(): void
    {
        Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);

        $staff = User::factory()->create(['username' => 'auth-users-5']);
        $this->grantPermissions($staff, ['view_users']); // tanpa create_users

        Livewire::actingAs($staff)->test(Users::class)
            ->set('username', 'usernew')
            ->set('name', 'User Baru')
            ->set('email', 'usernew@example.com')
            ->set('password', 'password')
            ->set('selectedRoles', ['Kasir'])
            ->call('save')
            ->assertForbidden();

        $this->assertFalse(User::where('username', 'usernew')->exists());
    }

    // -----------------------------------------------------------------
    // Cross-cutting: super admin bypass
    // -----------------------------------------------------------------

    public function test_super_admin_can_still_perform_all_write_actions(): void
    {
        $product = $this->makeProduct();
        $superAdmin = User::factory()->create(['username' => 'auth-superadmin']);
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->assignRole('Super Admin');

        Livewire::actingAs($superAdmin)->test(Products::class)
            ->call('delete', $product->id)
            ->assertOk();

        $this->assertNull(Product::find($product->id));
    }

    // -----------------------------------------------------------------
    // Users — filter status aktif/nonaktif
    // -----------------------------------------------------------------

    public function test_users_list_defaults_to_active_only(): void
    {
        $active = User::factory()->create(['username' => 'filter-active-1', 'name' => 'User Aktif']);
        $inactive = User::factory()->create(['username' => 'filter-inactive-1', 'name' => 'User Nonaktif']);
        $inactive->deactivate();

        $viewer = User::factory()->create(['username' => 'filter-viewer-1']);

        Livewire::actingAs($viewer)->test(Users::class)
            ->assertSee('User Aktif')
            ->assertDontSee('User Nonaktif');
    }

    public function test_users_list_inactive_filter_shows_only_deactivated_users(): void
    {
        $active = User::factory()->create(['username' => 'filter-active-2', 'name' => 'User Aktif Dua']);
        $inactive = User::factory()->create(['username' => 'filter-inactive-2', 'name' => 'User Nonaktif Dua']);
        $inactive->deactivate();

        $viewer = User::factory()->create(['username' => 'filter-viewer-2']);

        Livewire::actingAs($viewer)->test(Users::class)
            ->set('statusFilter', 'inactive')
            ->assertDontSee('User Aktif Dua')
            ->assertSee('User Nonaktif Dua');
    }

    public function test_users_list_all_filter_shows_both_active_and_inactive(): void
    {
        $active = User::factory()->create(['username' => 'filter-active-3', 'name' => 'User Aktif Tiga']);
        $inactive = User::factory()->create(['username' => 'filter-inactive-3', 'name' => 'User Nonaktif Tiga']);
        $inactive->deactivate();

        $viewer = User::factory()->create(['username' => 'filter-viewer-3']);

        Livewire::actingAs($viewer)->test(Users::class)
            ->set('statusFilter', 'all')
            ->assertSee('User Aktif Tiga')
            ->assertSee('User Nonaktif Tiga');
    }
}
