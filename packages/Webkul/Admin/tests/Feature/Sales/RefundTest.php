<?php

use Webkul\Checkout\Models\Cart;
use Webkul\Checkout\Models\CartItem;
use Webkul\Customer\Models\Customer;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderItem;
use Webkul\Sales\Models\OrderPayment;
use Webkul\Sales\Models\Refund;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

it('should return the refund index page', function () {
    // Act and Assert
    $this->loginAsAdmin();

    get(route('admin.sales.refunds.index'))
        ->assertOk()
        ->assertSeeText(trans('admin::app.sales.refunds.index.title'));
});

it('should faild the validation error when refund items data not provided', function () {
    // Arrange
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    CartItem::factory()->create([
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'type'       => $product->type,
        'name'       => $product->name,
        'cart_id'    => $cartId = Cart::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
        ])->id,
    ]);

    $order = Order::factory()->create([
        'cart_id'             => $cartId,
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
    ]);

    OrderItem::factory()->create([
        'product_id'   => $product->id,
        'order_id'     => $order->id,
        'qty_refunded' => $qty = rand(1, 2),
        'qty_invoiced' => $qty + 1,
        'type'         => $product->type,
    ]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
    ]);

    foreach ($order->items as $item) {
        foreach ($order->channel->inventory_sources as $inventorySource) {
            $items[$item->id] = $inventorySource->id;
        }
    }

    // Act and Assert
    $this->loginAsAdmin();

    postJson(route('admin.sales.refunds.store', $order->id))
        ->assertJsonValidationErrorFor('refund.items')
        ->assertUnprocessable();
});

it('should faild the validation error when refund items data provided with wrong way', function () {
    // Arrange
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    CartItem::factory()->create([
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'type'       => $product->type,
        'name'       => $product->name,
        'cart_id'    => $cartId = Cart::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
        ])->id,
    ]);

    $order = Order::factory()->create([
        'cart_id'             => $cartId,
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
    ]);

    OrderItem::factory()->create([
        'product_id'   => $product->id,
        'order_id'     => $order->id,
        'qty_refunded' => $qty = rand(1, 2),
        'qty_invoiced' => $qty + 1,
        'type'         => $product->type,
    ]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
    ]);

    foreach ($order->items as $item) {
        foreach ($order->channel->inventory_sources as $inventorySource) {
            $items[$item->id] = $inventorySource->id;
        }
    }

    // Act and Assert
    $this->loginAsAdmin();

    postJson(route('admin.sales.refunds.store', $order->id), [
        'refund' => [
            'items' => [
                fake()->word(),
            ],
        ],
    ])
        ->assertJsonValidationErrorFor('refund.items.0')
        ->assertUnprocessable();
});

it('should store the create page of refunds', function () {
    // Arrange
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    CartItem::factory()->create([
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'type'       => $product->type,
        'name'       => $product->name,
        'cart_id'    => $cartId = Cart::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
        ])->id,
    ]);

    $order = Order::factory()->create([
        'cart_id'             => $cartId,
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
    ]);

    OrderItem::factory()->create([
        'product_id'   => $product->id,
        'order_id'     => $order->id,
        'qty_refunded' => $qty = rand(1, 2),
        'qty_invoiced' => $qty + 1,
        'type'         => $product->type,
    ]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
    ]);

    foreach ($order->items as $item) {
        foreach ($order->channel->inventory_sources as $inventorySource) {
            $items[$item->id] = $inventorySource->id;
        }
    }

    // Act and Assert
    $this->loginAsAdmin();

    postJson(route('admin.sales.refunds.store', $order->id), [
        'refund' => [
            'items'             => $items,
            'shipping'          => 0,
            'adjustment_refund' => '0',
            'adjustment_fee'    => '0',
        ],
    ])
        ->assertRedirect(route('admin.sales.refunds.index'))
        ->isRedirection();

    $this->assertModelWise([
        Refund::class => [
            [
                'state'    => 'refunded',
                'order_id' => $order->id,
            ],
        ],
    ]);
});

it('should return the order refunded data', function () {
    // Arrange
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    CartItem::factory()->create([
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'type'       => $product->type,
        'name'       => $product->name,
        'cart_id'    => $cartId = Cart::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
        ])->id,
    ]);

    $order = Order::factory()->create([
        'cart_id'             => $cartId,
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
    ]);

    OrderItem::factory()->create([
        'product_id'   => $product->id,
        'order_id'     => $order->id,
        'qty_refunded' => $qty = rand(1, 2),
        'qty_invoiced' => $qty + 1,
    ]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
    ]);

    $summary = [
        'subtotal'    => ['price' => 0],
        'discount'    => ['price' => 0],
        'tax'         => ['price' => 0],
        'shipping'    => ['price' => 0],
        'grand_total' => ['price' => 0],
    ];

    foreach ($order->items as $item) {
        if ($item->qty_to_refund) {
            $items[$item->id] = rand(1, $item->qty_to_refund);
        }
    }

    foreach ($items as $orderItemId => $qty) {
        $orderItem = OrderItem::find($orderItemId);

        $summary['subtotal']['price'] += $orderItem->base_price * $qty;

        $summary['discount']['price'] += ($orderItem->base_discount_amount / $orderItem->qty_ordered) * $qty;

        $summary['tax']['price'] += ($orderItem->tax_amount / $orderItem->qty_ordered) * $qty;
    }

    $summary['shipping']['price'] += $order->base_shipping_invoiced - $order->base_shipping_refunded - $order->base_shipping_discount_amount;

    $summary['grand_total']['price'] += $summary['subtotal']['price'] + $summary['tax']['price'] + $summary['shipping']['price'] - $summary['discount']['price'];

    // Act and Assert
    $this->loginAsAdmin();

    postJson(route('admin.sales.refunds.update_qty', $order->id), $items)
        ->assertOk()
        ->assertJsonPath('grand_total.price', $summary['grand_total']['price']);
});

it('should return the view page of refund', function () {
    // Arrange
    $product = (new ProductFaker([
        'attributes' => [
            5 => 'new',
        ],

        'attribute_value' => [
            'new' => [
                'boolean_value' => true,
            ],
        ],
    ]))
        ->getSimpleProductFactory()
        ->create();

    $customer = Customer::factory()->create();

    CartItem::factory()->create([
        'product_id' => $product->id,
        'sku'        => $product->sku,
        'type'       => $product->type,
        'name'       => $product->name,
        'cart_id'    => $cartId = Cart::factory()->create([
            'customer_id'         => $customer->id,
            'customer_email'      => $customer->email,
            'customer_first_name' => $customer->first_name,
            'customer_last_name'  => $customer->last_name,
        ])->id,
    ]);

    $order = Order::factory()->create([
        'cart_id'             => $cartId,
        'customer_id'         => $customer->id,
        'customer_email'      => $customer->email,
        'customer_first_name' => $customer->first_name,
        'customer_last_name'  => $customer->last_name,
    ]);

    OrderItem::factory()->create([
        'product_id'   => $product->id,
        'order_id'     => $order->id,
        'qty_refunded' => $qty = rand(1, 2),
        'qty_invoiced' => $qty + 1,
    ]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
    ]);

    $refund = Refund::factory()->create([
        'order_id' => $order->id,
    ]);

    // Act and Assert
    $this->loginAsAdmin();

    get(route('admin.sales.refunds.view', $refund->id))
        ->assertOk()
        ->assertSeeText(trans('admin::app.sales.refunds.view.title', ['refund_id' => $refund->id]))
        ->assertSeeText(trans('admin::app.sales.refunds.view.product-ordered'));
});
