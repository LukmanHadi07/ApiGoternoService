<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use PhpParser\Node\NullableType;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request){
        $id = $request->input('id');
        $limit = $request->input('limit', 10);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['items_product'])->find($id);

            if ($transaction) {
                return ResponseFormatter::success($transaction, 'Data transaksi telah berhasil diambil');
            } else {
                return ResponseFormatter::error([
                    'message' => 'Data transaksi gagal diambil',
                     Null,
                     404
                ]);
            }
        }
          $transaction = Transaction::with(['items_product'])->where('users_id', Auth::user()->id);
          if ($status) {
            $transaction->where('status', $status);
          } 

          return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list succes diambil'
          );
    }

    public function checkout(Request $request){
      $request->validate([
          'items' => 'required|array',
          'items.*.id' => 'exists:products.id',
          'total_price' => 'required',
          'shipping_price' => 'required',
           'status' => 'required|in:PENDING, SUCCESS, CANCELLED, FAILED, SHIPPING, SHIPPED'
      ]);

      $transaction = Transaction::create([
        'users_id' => Auth::user()->id,
        'address' => $request->address,
        'total_price' => $request->total_price,
        'shipping_price' => $request->shipping_price,
        'status' => $request->status
      ]);

      foreach ($request->items as $product) {
        Transaction::create([
          'users_id' => Auth::user()->id,
          'products_id' => $product['id'],
          'transactions_id' => $transaction->id,
          // 'quantity' => $product['quantity']
          'quantity' => $product['quantity']
        ]);

        return ResponseFormatter::success($transaction, 'Checkout Success');
      }
    }
}
