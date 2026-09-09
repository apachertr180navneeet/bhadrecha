<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaundryOrder;
use App\Models\LaundryOrderItem;
use App\Models\Item;
use App\Models\Store;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class LaundryOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view laundry orders')->only(['index']);
        $this->middleware('permission:create laundry orders')->only(['store']);
        $this->middleware('permission:edit laundry orders')->only(['edit', 'update']);
        $this->middleware('permission:delete laundry orders')->only('destroy');
        $this->middleware('permission:receive laundry orders')->only(['getReceiveItems', 'updateReceiveItems']);
        $this->middleware('permission:pay laundry orders')->only(['markAsPaid']);
    }

    protected function getStoreBranchPermissions(Request $request = null)
    {
        $user = auth()->user();
        $userStoreId = $user->store_id;
        $userBranchId = $user->branch_id;

        $isManager = $user->hasRole('Manager')
            || $user->hasRole('Store Manager')
            || in_array(strtolower($user->role ?? ''), ['manager', 'store_manager', 'store manager']);

        $isAdmin = ($user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || in_array(strtolower($user->role ?? ''), ['admin', 'super_admin']))
            && !$isManager;

        // Managers or users with an assigned store (who are not Admins) are restricted to their store & branch
        if ($isManager || ($userStoreId && !$isAdmin)) {
            $canViewAllStores = false;
            $canViewAllBranches = false;
        } else {
            $canViewAllStores = $isAdmin;
            $canViewAllBranches = $isAdmin;
        }

        $selectedStoreId = (!$canViewAllStores && $userStoreId)
            ? $userStoreId
            : ($request ? $request->get('store_id') : null);

        $selectedBranchId = (!$canViewAllBranches && $userBranchId)
            ? $userBranchId
            : ($request ? $request->get('branch_id') : null);

        if (!$selectedStoreId && $selectedBranchId) {
            $branchObj = Branch::find($selectedBranchId);
            if ($branchObj) {
                $selectedStoreId = $branchObj->store_id;
            }
        }

        return [
            'user'               => $user,
            'isAdmin'            => $isAdmin,
            'isManager'          => $isManager,
            'userStoreId'        => $userStoreId,
            'userBranchId'       => $userBranchId,
            'canViewAllStores'   => $canViewAllStores,
            'canViewAllBranches' => $canViewAllBranches,
            'selectedStoreId'    => $selectedStoreId,
            'selectedBranchId'   => $selectedBranchId,
        ];
    }

    public function index(Request $request)
    {
        $perm = $this->getStoreBranchPermissions($request);
        $canViewAllStores   = $perm['canViewAllStores'];
        $canViewAllBranches = $perm['canViewAllBranches'];
        $selectedStoreId    = $perm['selectedStoreId'];
        $selectedBranchId   = $perm['selectedBranchId'];
        $userStoreId        = $perm['userStoreId'];
        $userBranchId       = $perm['userBranchId'];

        $orders = LaundryOrder::with(['store', 'branch', 'items.item'])
            ->when($selectedStoreId, fn($q) => $q->where('store_id', $selectedStoreId))
            ->when($selectedBranchId, fn($q) => $q->where('branch_id', $selectedBranchId))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('order_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('order_date', '<=', $request->date_to))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $items = Item::where('status', 1)->orderBy('name')->get();
        $stores = $canViewAllStores ? Store::all() : Store::where('id', $userStoreId)->get();
        $branches = Branch::when(!$canViewAllStores && $userStoreId, fn($q) => $q->where('store_id', $userStoreId))
            ->when(!$canViewAllBranches && $userBranchId, fn($q) => $q->where('id', $userBranchId))
            ->get();

        return view('admin.laundry-orders.index', compact(
            'orders', 'items', 'stores', 'branches',
            'selectedStoreId', 'selectedBranchId',
            'canViewAllStores', 'canViewAllBranches'
        ));
    }

    public function store(Request $request)
    {
        try {
            $perm = $this->getStoreBranchPermissions($request);
            if (!$perm['canViewAllStores'] && $perm['userStoreId']) {
                $request->merge(['store_id' => $perm['userStoreId']]);
            }
            if (!$perm['canViewAllBranches'] && $perm['userBranchId']) {
                $request->merge(['branch_id' => $perm['userBranchId']]);
            }

            $validator = Validator::make($request->all(), [
                'order_date'    => 'required|date',
                'store_id'      => 'required|exists:stores,id',
                'branch_id'     => 'required|exists:branches,id',
                'remark'        => 'nullable|string|max:1000',
                'order_items'   => 'required|array|min:1',
                'order_items.*.item_id' => 'required|exists:items,id',
                'order_items.*.qty'     => 'required|integer|min:1',
                'order_items.*.price'   => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->order_items as $row) {
                $totalAmount += $row['qty'] * $row['price'];
            }

            $order = LaundryOrder::create([
                'order_date'   => $request->order_date,
                'remark'       => $request->remark,
                'total_amount' => $totalAmount,
                'store_id'     => $request->store_id,
                'branch_id'    => $request->branch_id,
            ]);

            foreach ($request->order_items as $row) {
                $order->items()->create([
                    'item_id' => $row['item_id'],
                    'qty'     => $row['qty'],
                    'price'   => $row['price'],
                    'total'   => $row['qty'] * $row['price'],
                ]);
            }

            DB::commit();
            logActivity('Created', 'Laundry Order', "Created laundry order #{$order->id}");

            return response()->json(['success' => true, 'message' => 'Laundry order created successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $perm = $this->getStoreBranchPermissions();
        $order = LaundryOrder::with('items')
            ->when(!$perm['canViewAllStores'] && $perm['selectedStoreId'], fn($q) => $q->where('store_id', $perm['selectedStoreId']))
            ->when(!$perm['canViewAllBranches'] && $perm['selectedBranchId'], fn($q) => $q->where('branch_id', $perm['selectedBranchId']))
            ->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        try {
            $perm = $this->getStoreBranchPermissions($request);
            $order = LaundryOrder::when(!$perm['canViewAllStores'] && $perm['selectedStoreId'], fn($q) => $q->where('store_id', $perm['selectedStoreId']))
                ->when(!$perm['canViewAllBranches'] && $perm['selectedBranchId'], fn($q) => $q->where('branch_id', $perm['selectedBranchId']))
                ->findOrFail($id);

            if (!$perm['canViewAllStores'] && $perm['userStoreId']) {
                $request->merge(['store_id' => $perm['userStoreId']]);
            }
            if (!$perm['canViewAllBranches'] && $perm['userBranchId']) {
                $request->merge(['branch_id' => $perm['userBranchId']]);
            }

            $validator = Validator::make($request->all(), [
                'order_date'    => 'required|date',
                'store_id'      => 'required|exists:stores,id',
                'branch_id'     => 'required|exists:branches,id',
                'remark'        => 'nullable|string|max:1000',
                'order_items'   => 'required|array|min:1',
                'order_items.*.item_id' => 'required|exists:items,id',
                'order_items.*.qty'     => 'required|integer|min:1',
                'order_items.*.price'   => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->order_items as $row) {
                $totalAmount += $row['qty'] * $row['price'];
            }

            $order->update([
                'order_date'   => $request->order_date,
                'remark'       => $request->remark,
                'total_amount' => $totalAmount,
                'store_id'     => $request->store_id,
                'branch_id'    => $request->branch_id,
            ]);

            // Delete existing items and re-create
            $order->items()->delete();

            foreach ($request->order_items as $row) {
                $order->items()->create([
                    'item_id' => $row['item_id'],
                    'qty'     => $row['qty'],
                    'price'   => $row['price'],
                    'total'   => $row['qty'] * $row['price'],
                ]);
            }

            DB::commit();
            logActivity('Updated', 'Laundry Order', "Updated laundry order #{$order->id}");

            return response()->json(['success' => true, 'message' => 'Laundry order updated successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $perm = $this->getStoreBranchPermissions();
            $order = LaundryOrder::when(!$perm['canViewAllStores'] && $perm['selectedStoreId'], fn($q) => $q->where('store_id', $perm['selectedStoreId']))
                ->when(!$perm['canViewAllBranches'] && $perm['selectedBranchId'], fn($q) => $q->where('branch_id', $perm['selectedBranchId']))
                ->findOrFail($id);

            $order->items()->delete();
            $order->delete();
            logActivity('Deleted', 'Laundry Order', "Deleted laundry order #{$id}");

            return response()->json(['success' => true, 'message' => 'Laundry order deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getReceiveItems($id)
    {
        $perm = $this->getStoreBranchPermissions();
        $order = LaundryOrder::with('items.item')
            ->when(!$perm['canViewAllStores'] && $perm['selectedStoreId'], fn($q) => $q->where('store_id', $perm['selectedStoreId']))
            ->when(!$perm['canViewAllBranches'] && $perm['selectedBranchId'], fn($q) => $q->where('branch_id', $perm['selectedBranchId']))
            ->findOrFail($id);
        return response()->json($order);
    }

    public function updateReceiveItems(Request $request, $id)
    {
        try {
            $perm = $this->getStoreBranchPermissions($request);
            $order = LaundryOrder::with('items')
                ->when(!$perm['canViewAllStores'] && $perm['selectedStoreId'], fn($q) => $q->where('store_id', $perm['selectedStoreId']))
                ->when(!$perm['canViewAllBranches'] && $perm['selectedBranchId'], fn($q) => $q->where('branch_id', $perm['selectedBranchId']))
                ->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'order_items' => 'required|array|min:1',
                'order_items.*.id' => 'required|exists:laundry_order_items,id',
                'order_items.*.received_qty' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            foreach ($request->order_items as $row) {
                $item = $order->items->where('id', $row['id'])->first();
                if ($item) {
                    $item->update([
                        'received_qty' => $row['received_qty']
                    ]);
                }
            }

            // Reload items to calculate order status
            $order->load('items');

            $totalSent = $order->items->sum('qty');
            $totalReceived = $order->items->sum('received_qty');

            if ($totalReceived >= $totalSent) {
                $status = 'received';
            } elseif ($totalReceived > 0) {
                $status = 'partially_received';
            } else {
                $status = 'pending';
            }

            $order->update(['status' => $status]);

            DB::commit();
            logActivity('Updated', 'Laundry Order', "Updated received items for laundry order #{$order->id}");

            return response()->json(['success' => true, 'message' => 'Received items updated successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markAsPaid(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_ids'    => 'required|array|min:1',
                'order_ids.*'  => 'exists:laundry_orders,id',
                'payment_date' => 'required|date',
                'remarks'      => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $perm = $this->getStoreBranchPermissions($request);

            // Fetch unpaid orders only to prevent double payment!
            $orders = LaundryOrder::whereIn('id', $request->order_ids)
                ->when(!$perm['canViewAllStores'] && $perm['selectedStoreId'], fn($q) => $q->where('store_id', $perm['selectedStoreId']))
                ->when(!$perm['canViewAllBranches'] && $perm['selectedBranchId'], fn($q) => $q->where('branch_id', $perm['selectedBranchId']))
                ->where(function($q) {
                    $q->whereNull('is_paid')->orWhere('is_paid', false);
                })
                ->get();

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No unpaid orders selected or selected orders are already paid.'
                ], 400);
            }

            $totalAmount = $orders->sum('total_amount');
            $primaryOrder = $orders->first();

            // Ensure category ID 9 exists
            $category = ExpenseCategory::firstOrCreate(
                ['id' => 9],
                ['name' => 'Laundry Expense', 'status' => 1]
            );

            // Compute default remarks if not provided
            $minDate = $orders->min('order_date')->format('d M Y');
            $maxDate = $orders->max('order_date')->format('d M Y');
            $orderCount = $orders->count();

            $dateRangeStr = ($minDate == $maxDate) ? $minDate : "{$minDate} to {$maxDate}";
            $defaultRemark = "Laundry Payment for {$orderCount} order(s) (Date: {$dateRangeStr})";
            $finalRemark = $request->filled('remarks') ? $request->remarks : $defaultRemark;

            // Create Expense record with category_id = 9
            $expense = Expense::create([
                'store_id'    => $primaryOrder->store_id,
                'branch_id'   => $primaryOrder->branch_id,
                'category_id' => 9,
                'amount'      => $totalAmount,
                'date'        => $request->payment_date,
                'remarks'     => $finalRemark,
            ]);

            // Update orders to paid
            foreach ($orders as $order) {
                $order->update([
                    'is_paid'        => true,
                    'payment_status' => 'paid',
                    'paid_at'        => $request->payment_date,
                    'expense_id'     => $expense->id,
                ]);
            }

            DB::commit();

            logActivity('Created', 'Expense / Laundry Payment', "Recorded laundry payment expense #{$expense->id} for {$orderCount} order(s) totaling ₹{$totalAmount}");

            return response()->json([
                'success' => true,
                'message' => "Successfully marked {$orderCount} order(s) as paid and added ₹" . number_format($totalAmount, 2) . " to Expense (Category: 9).",
                'expense_id' => $expense->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
