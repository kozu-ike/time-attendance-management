<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StampCorrectionRequestAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;
    protected Attendance $attendance;

    protected StampCorrectionRequest $pendingRequest;
    protected StampCorrectionRequest $approvedRequest;


    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AdminSeeder::class);

        $this->admin = Admin::where('email', 'admin@example.com')->first();
        $this->user = User::factory()->create();

        $workDate = '2025-06-16';

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $workDate,
            'clock_in' => Carbon::parse("$workDate 08:30:00"),
            'clock_out' => Carbon::parse("$workDate 18:30:00"),
        ]);

        $this->pendingRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
            'requested_clock_in' => "$workDate 08:30:00",
            'requested_clock_out' => "$workDate 18:30:00",
            'requested_breaks_json' => json_encode([]),
            'note' => 'テスト用：承認待ち',
        ]);

        $this->approvedRequest = StampCorrectionRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status' => 'approved',
            'requested_clock_in' => "$workDate 08:45:00",
            'requested_clock_out' => "$workDate 18:15:00",
            'requested_breaks_json' => json_encode([]),
            'reviewed_at' => now(),
            'admin_id' => $this->admin->id,
            'note' => 'テスト用：承認済み',
        ]);
    }



    /** @test */
    public function admin_can_see_all_pending_correction_requests()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('stamp_correction_request.list', ['status' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee($this->pendingRequest->note);
        $response->assertDontSee($this->approvedRequest->note);
    }

    /** @test */
    public function admin_can_see_all_approved_correction_requests()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('stamp_correction_request.list', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee($this->approvedRequest->note);
        $response->assertDontSee($this->pendingRequest->note);
    }

    /** @test */
    public function admin_can_view_correction_request_detail()
    {

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('stamp_correction_request.edit_approve', $this->pendingRequest->id));

        $response->assertStatus(200);

        $response->assertSee('08:30');
        $response->assertSee('18:30');
        $response->assertSee($this->pendingRequest->note);
    }


    /** @test */
    public function admin_can_approve_correction_request_and_attendance_is_updated()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.stamp_correction_request.approve', $this->pendingRequest->id));

        // もし承認後にリダイレクトされるなら302に合わせてください
        $response->assertStatus(200);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $this->pendingRequest->id,
            'status' => 'approved',
            'admin_id' => $this->admin->id,
        ]);

        $updatedAttendance = Attendance::find($this->attendance->id);

        $this->assertEquals($this->pendingRequest->requested_clock_in, $updatedAttendance->clock_in);
        $this->assertEquals($this->pendingRequest->requested_clock_out, $updatedAttendance->clock_out);
    }
}
