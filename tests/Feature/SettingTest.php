<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use App\Models\Attendance;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_requires_authentication(): void
    {
        $response = $this->get('/settings');
        $response->assertStatus(302);
    }

    public function test_authenticated_user_can_view_settings_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Auto Check-Out Settings');
    }


    public function test_authenticated_user_can_update_auto_checkout_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/settings', [
            'university_name' => 'Test Institution',
            'enable_auto_checkout' => 'off',
            'auto_checkout_delay' => 45,
        ]);

        $response->assertStatus(302); // Redirect back
        $this->assertEquals('off', Setting::getValue('enable_auto_checkout'));
        $this->assertEquals('45', Setting::getValue('auto_checkout_delay'));
    }

    public function test_auto_checkout_logic_respects_settings(): void
    {
        config(['app.timezone' => 'UTC']);
        // Set shift times
        Setting::updateOrCreate(['key' => 'morning_shift_end'], ['value' => '12:00']);
        Setting::updateOrCreate(['key' => 'auto_checkout_delay'], ['value' => '30']);
        Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => 'on']);

        // Create teacher and attendance record with morning_in but no morning_out
        $teacher = Teacher::create([
            'name' => 'John Doe',
            'employee_id' => 'EMP123',
            'department' => 'IT',
            'status' => 'active'
        ]);

        // Yesterday's attendance (needs auto checkout since morning_out is null and time has passed)
        $date = Carbon::yesterday();
        $record = Attendance::create([
            'teacher_id' => $teacher->id,
            'date' => $date->toDateString(),
            'rfid_uid' => 'CARD123',
            'morning_in' => '08:00:00',
        ]);

        // 1. Run performAutoCheckout when enable_auto_checkout is off
        Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => 'off']);
        
        // Invoke the index route acting as admin which triggers performAutoCheckout
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard');

        $record->refresh();
        $this->assertNull($record->morning_out); // Should not have checked out since disabled

        // 2. Turn it on but current time is NOT past shift end + delay
        Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => 'on']);
        
        // Let's set carbon time to 12:15:00 yesterday (shift end + 15 mins, delay is 30 mins)
        Carbon::setTestNow($date->copy()->setTime(12, 15, 0));
        
        $this->actingAs($user)->get('/dashboard');
        
        $record->refresh();
        $this->assertNull($record->morning_out); // Should not check out yet (within delay)

        // 3. Set carbon time past shift end + delay (12:35:00)
        Carbon::setTestNow($date->copy()->setTime(12, 35, 0));
        
        $this->actingAs($user)->get('/dashboard');
        
        $record->refresh();
        $this->assertEquals('12:00', $record->morning_out); // Should have checked out automatically to 12:00
        $this->assertStringContainsString('[Auto Morning Checkout]', $record->manual_note);

        Carbon::setTestNow(); // Reset Carbon time
    }

    public function test_artisan_command_auto_checkout_respects_settings(): void
    {
        config(['app.timezone' => 'UTC']);
        Setting::updateOrCreate(['key' => 'morning_shift_end'], ['value' => '12:00']);
        Setting::updateOrCreate(['key' => 'auto_checkout_delay'], ['value' => '30']);
        Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => 'on']);

        $teacher = Teacher::create([
            'name' => 'Jane Doe',
            'employee_id' => 'EMP456',
            'department' => 'IT',
            'status' => 'active'
        ]);

        $date = Carbon::yesterday();
        $record = Attendance::create([
            'teacher_id' => $teacher->id,
            'date' => $date->toDateString(),
            'rfid_uid' => 'CARD456',
            'morning_in' => '08:00:00',
        ]);

        // 1. Run artisan command when enable_auto_checkout is off
        Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => 'off']);
        $this->artisan('attendance:auto-checkout')->assertExitCode(0);
        $record->refresh();
        $this->assertNull($record->morning_out);

        // 2. Set carbon time past shift end + delay (12:35:00) with settings ON
        Setting::updateOrCreate(['key' => 'enable_auto_checkout'], ['value' => 'on']);
        Carbon::setTestNow($date->copy()->setTime(12, 35, 0));

        $this->artisan('attendance:auto-checkout')
            ->expectsOutput('Starting auto-checkout process...')
            ->expectsOutput('Auto-checkout completed. Updated 1 records.')
            ->assertExitCode(0);

        $record->refresh();
        $this->assertEquals('12:00', $record->morning_out);
        $this->assertStringContainsString('[Auto Morning Checkout]', $record->manual_note);

        Carbon::setTestNow(); // Reset Carbon time
    }
}
