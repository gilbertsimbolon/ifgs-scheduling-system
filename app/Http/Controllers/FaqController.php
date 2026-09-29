<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Tampilkan daftar seluruh FAQ Landing Page.
     */
    public function index(Request $request): View
    {
        $query = Faq::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $faqs = $query->ordered()->paginate(10)->withQueryString();

        return view('faq.index', compact('faqs'));
    }

    /**
     * Simpan FAQ baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'question.required' => 'Pertanyaan FAQ wajib diisi.',
            'answer.required' => 'Jawaban FAQ wajib diisi.',
        ]);

        $maxOrder = Faq::max('order') ?? 0;

        Faq::create([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'order' => $validated['order'] ?? ($maxOrder + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('faqs.index')
            ->with('success', 'Pertanyaan FAQ baru berhasil ditambahkan.');
    }

    /**
     * Perbarui FAQ.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'question.required' => 'Pertanyaan FAQ wajib diisi.',
            'answer.required' => 'Jawaban FAQ wajib diisi.',
            'order.required' => 'Nomor urutan wajib diisi.',
        ]);

        $faq->update([
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'order' => $validated['order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('faqs.index')
            ->with('success', 'Pertanyaan FAQ berhasil diperbarui.');
    }

    /**
     * Toggle status aktif / non-aktif FAQ.
     */
    public function toggleStatus(Request $request, Faq $faq): RedirectResponse|JsonResponse
    {
        $faq->update(['is_active' => ! $faq->is_active]);

        $label = $faq->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $faq->is_active,
                'message' => "FAQ berhasil {$label}.",
            ]);
        }

        return redirect()->route('faqs.index')
            ->with('success', "FAQ berhasil {$label}.");
    }

    /**
     * Hapus FAQ.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('faqs.index')
            ->with('success', 'Pertanyaan FAQ berhasil dihapus.');
    }
}
