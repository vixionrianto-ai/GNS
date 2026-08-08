{{-- ================= FILTER ENTERPRISE ================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-filter text-primary"></i>

            Filter Laporan

        </h5>

    </div>

    <div class="card-body">

        <form method="GET" action="{{ route('laporan.index') }}">

            <div class="row">

                {{-- Dari Tanggal --}}
                <div class="col-lg-3 col-md-6 mb-3">

                    <label>Dari Tanggal</label>

                    <input
                        type="date"
                        name="tanggal_awal"
                        class="form-control"
                        value="{{ request('tanggal_awal') }}"
                    >

                </div>

                {{-- Sampai --}}
                <div class="col-lg-3 col-md-6 mb-3">

                    <label>Sampai Tanggal</label>

                    <input
                        type="date"
                        name="tanggal_akhir"
                        class="form-control"
                        value="{{ request('tanggal_akhir') }}"
                    >

                </div>

                {{-- Bulan --}}
                <div class="col-lg-2 col-md-4 mb-3">

                    <label>Bulan</label>

                    <select
                        class="form-control"
                        name="bulan">

                        <option value="">Semua</option>

                        @for($i=1;$i<=12;$i++)

                            <option
                                value="{{ $i }}"
                                @selected(request('bulan')==$i)
                            >

                                {{ DateTime::createFromFormat('!m',$i)->format('F') }}

                            </option>

                        @endfor

                    </select>

                </div>

                {{-- Tahun --}}
                <div class="col-lg-2 col-md-4 mb-3">

                    <label>Tahun</label>

                    <select
                        class="form-control"
                        name="tahun">

                        <option value="">Semua</option>

                        @for($i=date('Y');$i>=2024;$i--)

                            <option
                                value="{{ $i }}"
                                @selected(request('tahun')==$i)
                            >

                                {{ $i }}

                            </option>

                        @endfor

                    </select>

                </div>

                {{-- Status --}}
                <div class="col-lg-2 col-md-4 mb-3">

                    <label>Status</label>

                    <select
                        class="form-control"
                        name="status">

                        <option value="">Semua</option>

                        <option
                            value="Lunas"
                            @selected(request('status')=='Lunas')>

                            Lunas

                        </option>

                        <option
                            value="Sebagian"
                            @selected(request('status')=='Sebagian')>

                            Sebagian

                        </option>

                        <option
                            value="Belum Bayar"
                            @selected(request('status')=='Belum Bayar')>

                            Belum Bayar

                        </option>

                        <option
                            value="Jatuh Tempo"
                            @selected(request('status')=='Jatuh Tempo')>

                            Jatuh Tempo

                        </option>

                    </select>

                </div>

            </div>

            <hr>

            <div class="d-flex justify-content-end">

                <a
                    href="{{ route('laporan.index') }}"
                    class="btn btn-secondary mr-2">

                    <i class="fas fa-undo"></i>

                    Reset

                </a>

                <button
                    class="btn btn-primary">

                    <i class="fas fa-search"></i>

                    Terapkan Filter

                </button>

            </div>

        </form>

    </div>