<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $item->nama }}</td>
    <td>{{ $item->status_peserta }}</td>
    <td>{{ $item->leads }}</td>
    <td>{{ $item->provinsi_nama }}</td>
    <td>{{ $item->kota_nama }}</td>
    <td>{{ $item->nama_bisnis }}</td>
    <td>{{ $item->jenisbisnis }}</td>
    <td>{{ $item->no_wa }}</td> 
    <td>{{ $item->situasi_bisnis }}</td>
    <td>{{ $item->kendala }}</td>

    <td>
        @if($item->kelas_id)
            {{ $item->kelas->nama_kelas }}
        @else
            -
        @endif
    </td>

    @if(auth()->user()->email == 'mbchamasah@gmail.com')
        <td>{{ $item->created_by }}</td>
        <td>{{ $item->created_by_role }}</td>
    @endif

    {{-- Action button kamu tetap sama --}}
    <td>
        {{-- tombol & script seperti sebelumnya --}}
    </td>
</tr>