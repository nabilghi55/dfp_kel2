@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.shop') }}">Shop</a></li>
                    <li class="breadcrumb-item">Cart</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="section-9 pt-4">
        <div class="container">
            <div class="row">
                @if (Session::has('success'))
                    <div class="col-md-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {!! Session::get('success') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if (Session::has('error'))
                    <div class="col-md-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ Session::get('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                @if (Cart::count() > 0)
                    <div class="col-md-8">
                        <div class="card-body table-responsive p-0">
                            <table id="cart" class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (Cart::content() as $item)
                                        <tr>
                                            <td class="text-start">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $item->options->image }}" width="100" />
                                                    {{-- Mengakses product_image --}}
                                                    <h6>{{ $item->name }}</h6>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="input-group quantity mx-auto" style="width: 100px;">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-dark btn-minus p-2 pt-1 pb-1 sub"
                                                            data-id="{{ $item->rowId }}">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input readonly type="text"
                                                        class="form-control form-control-sm border-0 text-center"
                                                        value="{{ $item->qty }}">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-dark btn-plus p-2 pt-1 pb-1 add"
                                                            data-id="{{ $item->rowId }}">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Rp {{ number_format($item->price, 2) }}</td>
                                            <td>Rp {{ number_format($item->price * $item->qty, 2) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteItem('{{ $item->rowId }}');"><i
                                                        class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4 mt-2">
                        <div class="card cart-summery">
                            <div class="card-body">
                                <div class="sub-title">
                                    <h2 class="bg-white">Cart Summary</h2>
                                </div>
                                <div class="d-flex justify-content-between pb-2">
                                    <div>Subtotal</div>
                                    <div>Rp {{ Cart::subtotal() }}</div>
                                </div>
                                <div class="pt-5">
                                    <a href="{{ route('front.checkout') }}" class="btn-dark btn btn-block w-100">Proceed to
                                        Checkout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <h4>Your Cart is Empty</h4>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
@section('customJs')
    <script>
        $('.add').click(function() {
            console.log('Plus button clicked');
            var qtyElement = $(this).parent().prev(); // Qty Input
            var qtyValue = parseInt(qtyElement.val());
            console.log('Current quantity:', qtyValue);
            if (qtyValue < 10) {

                qtyElement.val(qtyValue + 1);
                var rowId = $(this).data('id');
                var newQty = qtyElement.val();
                updateCart(rowId, newQty)
            }
        });

        $('.sub').click(function() {
            var qtyElement = $(this).parent().next();
            var qtyValue = parseInt(qtyElement.val());
            if (qtyValue > 1) {
                qtyElement.val(qtyValue - 1);

                var rowId = $(this).data('id');
                var newQty = qtyElement.val();
                updateCart(rowId, newQty)
            }
        });

        function updateCart(rowId, qty) {
            $.ajax({
                url: '{{ route('front.updateCart') }}',
                type: 'post',
                data: {
                    rowId: rowId,
                    qty: qty
                },
                dataType: 'json',
                success: function(response) {
                    window.location.href = '{{ route('front.cart') }}'

                }
            });
        }

        function deleteItem(rowId) {
            Swal.fire({
                icon: 'question',
                title: 'Confirm Deletion',
                text: 'Are you sure you want to delete this item?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('front.deleteItem.cart') }}',
                        type: 'post',
                        data: {
                            rowId: rowId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == true) {
                                window.location.href = '{{ route('front.cart') }}';
                            }
                        }
                    });
                }
            });
        }
    </script>

    <script>
        $(function() {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "searching": false,
                "paging": false,
                "info": false,
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@endsection
