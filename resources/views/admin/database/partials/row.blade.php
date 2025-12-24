   <tr 
    data-created-by="{{ strtolower($item->created_by) }}"
    data-bulan="{{ \Carbon\Carbon::parse($item->created_at)->month }}"
    data-year="{{ \Carbon\Carbon::parse($item->created_at)->year }}"
    data-id="{{ $item->id }}"
>

                            <td>{{ $loop->iteration ?? '-' }}</td>
                            <td contenteditable="true" class="editable" data-field="nama">{{ $item->nama }}</td>
                     <td>
                         <select class="form-control form-control-sm select-sumber" data-id="{{ $item->id }}">
                                    <option value="">- Pilih Sumber Leads -</option>
                                    <option value="Marketing" {{ $item->leads == 'Marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="Iklan" {{ $item->leads == 'Iklan' ? 'selected' : '' }}>Iklan</option>
                                    <option value="Alumni" {{ $item->leads == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                                    <option value="Mandiri" {{ $item->leads == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
                                </select>


                            </td>
     
        
    @if(strtolower(auth()->user()->role) !== 'administrator')
        <td>
            <select class="form-control form-control-sm select-provinsi" data-id="{{ $item->id }}" data-nama="{{ $item->provinsi_nama }}">
                <option value="">{{ $item->provinsi_nama ?: '-- Pilih Provinsi --' }}</option>
            </select>
        </td>
    @endif
    <td>
        <select class="form-control form-control-sm select-kota" data-id="{{ $item->id }}" data-prov-id="{{ $item->provinsi_id }}" data-nama="{{ $item->kota_nama }}">
             <option value="">{{ $item->kota_nama ?: '-- Pilih Kota --' }}</option>
        </select>
    </td>
                            <td contenteditable="true" class="editable" data-field="nama_bisnis">{{ $item->nama_bisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="jenis_bisnis">{{ $item->jenis_bisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="no_wa">{{ $item->no_wa }}</td>
                            <td>
                                @php $waNumber = preg_replace('/^0/', '62', $item->no_wa); @endphp
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-success btn-sm wa-button">
                                    <i class="bi bi-whatsapp" style="color:#fff;font-size:1.5rem;"></i>
                                </a>
                            </td>
                            <td contenteditable="true" class="editable" data-field="situasi_bisnis">{{ $item->situasi_bisnis }}</td>
                            <td contenteditable="true" class="editable" data-field="kendala">{{ $item->kendala }}</td>
                            @if(strtolower(auth()->user()->role) !== 'marketing')
                            <td>
                                <select class="form-control form-control-sm select-potensi" data-id="{{ $item->id }}">
                                    <option value="">- Pilih Kelas -</option>
                                    @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            @endif
                                @if(strtolower(auth()->user()->role) !== 'administrator'  && Auth::user()->role !== 'marketing')
                            <td>
                                <form action="{{ route('data.pindahKeSalesPlan', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-arrow-right"></i></button>
                                </form>
                            </td>
                            @endif
                            @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
                            <td>{{ $item->created_by }}</td>
                            <td>{{ $item->created_by_role }}</td>
                            @endif
                            
                            <td>
                                <a href="{{ route('admin.database.show', $item->id) }}" class="btn btn-info btn-sm">
                                    <i class="fa-solid fa-eye" style="color:#fff;"></i>
                                </a>
                                <form action="{{ route('delete-database', $item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-delete">
                                        <i class="fa-solid fa-trash" style="color:#fff;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
