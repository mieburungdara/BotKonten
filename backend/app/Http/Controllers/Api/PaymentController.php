<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Media;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'media_id' => 'nullable|exists:media,id',
            'album_id' => 'nullable|exists:albums,id',
            'payment_method' => 'nullable|string|in:simulation,manual',
        ]);

        $mediaId = $request->input('media_id');
        $albumId = $request->input('album_id');

        if (!$mediaId && !$albumId) {
            return response()->json(['error' => 'Media or album required'], 400);
        }

        $item = null;
        $amount = 0;

        if ($mediaId) {
            $media = Media::findOrFail($mediaId);
            $item = $media;
            $amount = $media->price;
        } elseif ($albumId) {
            $album = Album::findOrFail($albumId);
            $item = $album;
            $amount = $album->price;
        }

        if ($amount <= 0) {
            return response()->json(['message' => 'Free item - no payment needed'], 200);
        }

        $purchase = Purchase::create([
            'user_id' => $request->user()->id,
            'media_id' => $mediaId,
            'album_id' => $albumId,
            'amount' => $amount,
            'payment_method' => $request->input('payment_method', 'simulation'),
            'payment_status' => 'pending',
            'transaction_id' => 'txn_' . Str::random(16),
        ]);

        Log::info('Checkout initiated', [
            'purchase_id' => $purchase->id,
            'amount' => $amount,
            'payment_method' => $purchase->payment_method,
        ]);

        return response()->json([
            'purchase' => $purchase,
            'item' => $item,
            'amount' => $amount,
        ]);
    }

    public function simulatePayment(Request $request, $id)
    {
        $purchase = Purchase::with(['media', 'album'])->where('user_id', $request->user()->id)->findOrFail($id);

        $purchase->update([
            'payment_status' => 'completed',
        ]);

        // Send notification to buyer
        \App\Models\Notification::create([
            'user_id' => $purchase->user_id,
            'type' => 'payment_completed',
            'title' => 'Pembayaran Berhasil!',
            'message' => "Pembayaran sebesar Rp " . number_format($purchase->amount) . " telah berhasil. Terima kasih atas pembelian Anda!",
            'data' => ['purchase_id' => $purchase->id],
        ]);

        // Send notification to seller
        $sellerId = $purchase->media ? $purchase->media->user_id : $purchase->album->user_id;
        $itemName = $purchase->media ? $purchase->media->caption : $purchase->album->caption;

        \App\Models\Notification::create([
            'user_id' => $sellerId,
            'type' => 'sale_completed',
            'title' => 'Penjualan Baru!',
            'message' => "Selamat! '{$itemName}' telah terjual seharga Rp " . number_format($purchase->amount) . ".",
            'data' => ['purchase_id' => $purchase->id, 'buyer_id' => $purchase->user_id],
        ]);

        Log::info('Payment simulated', ['purchase_id' => $purchase->id]);

        return response()->json([
            'purchase' => $purchase,
            'message' => 'Payment successful',
        ]);
    }

    public function myPurchases(Request $request)
    {
        $purchases = Purchase::with(['media', 'album'])
            ->where('user_id', $request->user()->id)
            ->where('payment_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($purchases);
    }

    public function purchaseHistory(Request $request)
    {
        $purchases = Purchase::with(['media', 'album'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSpent = $purchases->where('payment_status', 'completed')->sum('amount');

        return response()->json([
            'purchases' => $purchases,
            'total_spent' => $totalSpent,
        ]);
    }
}