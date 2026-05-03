@foreach($data as $item)
    @include('admin.database.partials.row', ['item' => $item, 'loop' => $loop, 'kelas' => $kelas])
@endforeach
