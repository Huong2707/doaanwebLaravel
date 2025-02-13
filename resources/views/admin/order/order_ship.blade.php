@extends('admin.main')
@section('header')
    <!-- Custom styles for this page -->
    <link href="/template/admin/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Vận đơn</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                        <tr >
                            <th style="width: 5%">Mã</th>
                            <th style="width: 20%">Nhân viên</th>
                            <th style="width: 25%">Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Thời gian</th>
                            <th>Lựa chọn</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <th>{{$order->id}}</th>
                                <th>{{ \Illuminate\Support\Facades\DB::table('users')->where('id', $order->id_saler)->first()?->name ?? 'Unknown User' }}</th>
                                <th>{{ \Illuminate\Support\Facades\DB::table('users')->where('id', $order->id_customer)->first()?->name ?? 'Unknown User' }}</th>
                                <th>{{$order->total_money}}</th>
                                <th>{{$order->created_at}}</th>
                                <th>
                                    <a class="btn btn-primary" href="/admin/order/shipUpdate/{{$order->id}}">Hoàn thành</a>
                                    <a class="btn btn-warning" href="/admin/order/shipCancel/{{$order->id}}">Hủy đơn</a>
                                </th>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    <!-- Page level plugins -->
    <script src="/template/admin/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="/template/admin/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="/template/admin/js/demo/datatables-demo.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function removeRow(id, url) {
            if (confirm('Xóa mà không thể khôi phục. Bạn có chắc ?')) {
                $.ajax({
                    type: 'DELETE',
                    datatype: 'JSON',
                    data: { id },
                    url: url,
                    success: function (result) {
                        if (result.error === false) {
                            alert(result.message);
                            location.reload();
                        } else {
                            alert('Xóa lỗi vui lòng thử lại');
                        }
                    }
                })
            }
        }
    </script>
@endsection
