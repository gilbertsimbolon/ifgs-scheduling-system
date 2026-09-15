<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserQrCodeController extends Controller
{
    /**
     * Download the QR Code image as an SVG file.
     */
    public function download(Request $request, ?User $user = null): Response
    {
        $targetUser = $user ?? $request->user();

        // Authorize: only owner or staff (Admin/Manager/Kasir) can access
        if ($targetUser->id !== $request->user()->id && ! $request->user()->hasAnyRole(['Admin/Manager', 'Kasir'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh QR Code ini.');
        }

        $svgContent = QrCode::size(400)->margin(2)->generate($targetUser->qr_code ?? $targetUser->id);
        $filename = 'qrcode-'.$targetUser->slug.'.svg';

        return response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * View raw SVG QR Code for embedding in <img> tags.
     */
    public function svg(Request $request, User $user): Response
    {
        // Authorize: only owner or staff (Admin/Manager/Kasir) can access
        if ($user->id !== $request->user()->id && ! $request->user()->hasAnyRole(['Admin/Manager', 'Kasir'])) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $svgContent = QrCode::size(300)->margin(1)->generate($user->qr_code ?? $user->id);

        return response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /**
     * Display a printable digital member card with gym branding & QR Code.
     */
    public function printCard(Request $request, User $user): View
    {
        // Authorize: only owner or staff (Admin/Manager/Kasir) can access
        if ($user->id !== $request->user()->id && ! $request->user()->hasAnyRole(['Admin/Manager', 'Kasir'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak kartu member ini.');
        }

        $user->load(['member.memberships.product', 'roles']);
        $member = $user->member;
        $activeMembership = $member ? $member->activeMembership() : null;

        return view('member.card', [
            'user' => $user,
            'member' => $member,
            'activeMembership' => $activeMembership,
        ]);
    }
}
