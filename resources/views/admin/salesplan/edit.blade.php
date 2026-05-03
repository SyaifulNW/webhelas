@extends('layouts.masteradmin')

@section('content')
<div class="container mt-4">
    <h3>Edit Sales Plan</h3>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.salesplan.update', $plan->id) }}">
                @csrf
                @method('PUT')

                {{-- Potensi --}}
                <!-- <div class="form-group">
                    <label for="nominal">Potensi</label>
                    <input
                        type="number"
                        name="nominal"
                        class="form-control"
                        id="nominal"
                        value="{{ old('nominal', $plan->nominal) }}"
                        placeholder="Masukkan nominal">
                </div> -->

                {{-- Keterangan --}}
                <!-- <div class="form-group">
                    <label>Keterangan</label>
                    <textarea
                        name="keterangan"
                        class="form-control"
                        rows="2"
                        placeholder="Masukkan keterangan">{{ old('keterangan', $plan->keterangan) }}</textarea>
                </div> -->

                {{-- Status --}}
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="cold" {{ $plan->status == 'cold' ? 'selected' : '' }}>Cold</option>
                        <option value="warm" {{ $plan->status == 'warm' ? 'selected' : '' }}>Mau Transfer</option>
                        <option value="hot" {{ $plan->status == 'hot' ? 'selected' : '' }}>Sudah Transfer</option>
                        <option value="no" {{ $plan->status == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="{{ route('admin.salesplan.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
