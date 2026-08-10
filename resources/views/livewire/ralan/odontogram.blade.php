@php
    $listOdonto = collect($listPemeriksaanOdontogram ?? []);
    $totalPemeriksaan = $listOdonto->count();
    $countHilang = $listOdonto->where('value', 'Tanggal')->count();
    $countKaries = $listOdonto->where('value', 'Karies')->count();
    $countAkar = $listOdonto->where('value', 'Akar')->count();
    $countTumpat = $listOdonto->where('value', 'Tumpat')->count();
    $countFraktur = $listOdonto->where('value', 'Fraktur Mahkota')->count();

    $getTooth = function($key) use ($listOdonto) {
        return $listOdonto->where('gg_xx', $key)->first();
    };
@endphp

<div @if($isCollapsed) class="card card-info collapsed-card shadow-sm" @else class="card card-info shadow-sm" @endif>
    <div class="card-header bg-info text-white d-flex align-items-center">
        <h3 class="card-title font-weight-bold mb-0 flex-grow-1">
            <i class="fas fa-tooth mr-2"></i> Pemeriksaan Odontogram
        </h3>
        <div class="card-tools ml-auto">
            <button type="button" wire:click="collapsed" class="btn btn-tool text-white" data-card-widget="collapse">
                <i wire:ignore class="fas fa-lg @if($isCollapsed) fa-plus @else fa-minus @endif"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Summary Stats Bar -->
        <div class="row align-items-stretch mb-4 odontogram-stats-bar">
            <div class="col-6 col-sm-4 col-md-2 mb-2 d-flex">
                <div class="stat-card border-left-info shadow-sm rounded p-2 text-center bg-white w-100 d-flex flex-column justify-content-between">
                    <div class="stat-title text-xs font-weight-bold text-info text-uppercase mb-1 d-flex align-items-center justify-content-center" style="min-height: 28px; line-height: 1.2;">Total Periksa</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPemeriksaan }}</div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 mb-2 d-flex">
                <div class="stat-card border-left-danger shadow-sm rounded p-2 text-center bg-white w-100 d-flex flex-column justify-content-between">
                    <div class="stat-title text-xs font-weight-bold text-danger text-uppercase mb-1 d-flex align-items-center justify-content-center" style="min-height: 28px; line-height: 1.2;">Hilang (Tanggal)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $countHilang }}</div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 mb-2 d-flex">
                <div class="stat-card border-left-warning shadow-sm rounded p-2 text-center bg-white w-100 d-flex flex-column justify-content-between">
                    <div class="stat-title text-xs font-weight-bold text-warning text-uppercase mb-1 d-flex align-items-center justify-content-center" style="min-height: 28px; line-height: 1.2;">Karies</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $countKaries }}</div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 mb-2 d-flex">
                <div class="stat-card border-left-dark shadow-sm rounded p-2 text-center bg-white w-100 d-flex flex-column justify-content-between">
                    <div class="stat-title text-xs font-weight-bold text-dark text-uppercase mb-1 d-flex align-items-center justify-content-center" style="min-height: 28px; line-height: 1.2;">Sisa Akar</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $countAkar }}</div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 mb-2 d-flex">
                <div class="stat-card border-left-success shadow-sm rounded p-2 text-center bg-white w-100 d-flex flex-column justify-content-between">
                    <div class="stat-title text-xs font-weight-bold text-success text-uppercase mb-1 d-flex align-items-center justify-content-center" style="min-height: 28px; line-height: 1.2;">Tumpatan</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $countTumpat }}</div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-md-2 mb-2 d-flex">
                <div class="stat-card border-left-secondary shadow-sm rounded p-2 text-center bg-white w-100 d-flex flex-column justify-content-between">
                    <div class="stat-title text-xs font-weight-bold text-secondary text-uppercase mb-1 d-flex align-items-center justify-content-center" style="min-height: 28px; line-height: 1.2;">Fraktur Mahkota</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $countFraktur }}</div>
                </div>
            </div>
        </div>

        <!-- Odontogram Chart Section -->
        <div class="odontogram-wrapper mb-4 p-3 bg-light rounded border">
            <!-- Header quadrant labels -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                <span class="badge badge-pill badge-primary"><i class="fas fa-arrow-left mr-1"></i> KANAN (RIGHT)</span>
                <span class="font-weight-bold text-secondary text-sm">CHART ODONTOGRAM</span>
                <span class="badge badge-pill badge-primary">KIRI (LEFT) <i class="fas fa-arrow-right ml-1"></i></span>
            </div>

            <!-- Gigi Permanen Atas Label -->
            <div class="text-center mb-1">
                <span class="badge badge-info px-3 py-1 font-weight-normal text-xs">Gigi Permanen - Maxilla (Atas)</span>
            </div>

            <div class="table-odontogram-container overflow-auto">
                <table class="table-odontogram-grid">
                    <!-- Numbers row Gigi Permanen Atas -->
                    <tr class="tooth-number-row text-muted">
                        @foreach([18,17,16,15,14,13,12,11, 21,22,23,24,25,26,27,28] as $num)
                            <td>{{ $num % 10 }}</td>
                        @endforeach
                    </tr>
                    <!-- Teeth row Gigi Permanen Atas -->
                    <tr>
                        @foreach(['gg_18','gg_17','gg_16','gg_15','gg_14'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_13','gg_12','gg_11','gg_21','gg_22','gg_23'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_24','gg_25','gg_26','gg_27','gg_28'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                    </tr>

                    <!-- Spacer -->
                    <tr style="height: 10px;"></tr>

                    <!-- Gigi Desidui Atas label -->
                    <tr>
                        <td colspan="16" class="text-center py-1">
                            <span class="badge badge-secondary px-3 py-1 font-weight-normal text-xs">Gigi Desidui (Anak) - Atas</span>
                        </td>
                    </tr>

                    <!-- Teeth row Gigi Desidui Atas -->
                    <tr>
                        <td colspan="3" class="border-0"></td>
                        @foreach(['gg_55','gg_54'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_53','gg_52','gg_51','gg_61','gg_62','gg_63'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_64','gg_65'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        <td colspan="3" class="border-0"></td>
                    </tr>
                    <!-- Numbers row Gigi Desidui Atas & Bawah -->
                    <tr class="tooth-number-row text-muted">
                        <td colspan="3" class="border-0"></td>
                        @foreach([55,54,53,52,51, 61,62,63,64,65] as $num)
                            <td>{{ $num % 10 }}</td>
                        @endforeach
                        <td colspan="3" class="border-0"></td>
                    </tr>
                    <!-- Teeth row Gigi Desidui Bawah -->
                    <tr>
                        <td colspan="3" class="border-0"></td>
                        @foreach(['gg_85','gg_84'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_83','gg_82','gg_81','gg_71','gg_72','gg_73'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_74','gg_75'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        <td colspan="3" class="border-0"></td>
                    </tr>

                    <!-- Gigi Desidui Bawah label -->
                    <tr>
                        <td colspan="16" class="text-center py-1">
                            <span class="badge badge-secondary px-3 py-1 font-weight-normal text-xs">Gigi Desidui (Anak) - Bawah</span>
                        </td>
                    </tr>

                    <!-- Spacer -->
                    <tr style="height: 10px;"></tr>

                    <!-- Teeth row Gigi Permanen Bawah -->
                    <tr>
                        @foreach(['gg_48','gg_47','gg_46','gg_45','gg_44'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_43','gg_42','gg_41','gg_31','gg_32','gg_33'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_34','gg_35','gg_36','gg_37','gg_38'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" data-tooth="{{ $k }}" onclick="selectTooth('{{ $k }}', 'Gigi {{ $num }}')" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ url('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                    </tr>
                    <!-- Numbers row Gigi Permanen Bawah -->
                    <tr class="tooth-number-row text-muted">
                        @foreach([48,47,46,45,44,43,42,41, 31,32,33,34,35,36,37,38] as $num)
                            <td>{{ $num % 10 }}</td>
                        @endforeach
                    </tr>
                </table>
            </div>

            <!-- Gigi Permanen Bawah Label -->
            <div class="text-center mt-1">
                <span class="badge badge-info px-3 py-1 font-weight-normal text-xs">Gigi Permanen - Mandibula (Bawah)</span>
            </div>

            <!-- Legend Bar -->
            <div class="legend-bar mt-3 pt-2 border-top d-flex flex-wrap justify-content-center gap-3 text-xs">
                <div class="legend-item d-flex align-items-center mr-3 mb-1">
                    <img src="{{ url('images/Tanggal.png') }}" class="mr-1" style="height: 16px;">
                    <span>Tanggal (Hilang)</span>
                </div>
                <div class="legend-item d-flex align-items-center mr-3 mb-1">
                    <img src="{{ url('images/Karies.png') }}" class="mr-1" style="height: 16px;">
                    <span>Karies</span>
                </div>
                <div class="legend-item d-flex align-items-center mr-3 mb-1">
                    <img src="{{ url('images/Akar.png') }}" class="mr-1" style="height: 16px;">
                    <span>Sisa Akar</span>
                </div>
                <div class="legend-item d-flex align-items-center mr-3 mb-1">
                    <img src="{{ url('images/Tumpat.png') }}" class="mr-1" style="height: 16px;">
                    <span>Tumpatan</span>
                </div>
                <div class="legend-item d-flex align-items-center mb-1">
                    <span class="badge badge-secondary mr-1">F</span>
                    <span>Fraktur Mahkota</span>
                </div>
            </div>
        </div>

        <!-- Interactive Condition Quick Selector & Form Section -->
        <div class="card card-outline card-info shadow-xs mb-4">
            <div class="card-body">
                <!-- Condition Quick Selectors (Pills) -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold text-dark text-sm">
                        <i class="fas fa-magic text-info mr-1"></i> Pilih Kondisi Cepat:
                    </label>
                    <div class="d-flex flex-wrap align-items-center gap-2 condition-pills-container">
                        <button type="button" class="btn btn-outline-danger btn-sm condition-pill" data-condition="Tanggal" onclick="selectCondition('Tanggal')">
                            <img src="{{ url('images/Tanggal.png') }}" class="pill-icon mr-1" alt="Tanggal" style="height: 16px; vertical-align: middle;">
                            <span>Tanggal (Hilang)</span>
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm condition-pill text-dark" data-condition="Karies" onclick="selectCondition('Karies')">
                            <img src="{{ url('images/Karies.png') }}" class="pill-icon mr-1" alt="Karies" style="height: 16px; vertical-align: middle;">
                            <span>Karies</span>
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm condition-pill" data-condition="Akar" onclick="selectCondition('Akar')">
                            <img src="{{ url('images/Akar.png') }}" class="pill-icon mr-1" alt="Sisa Akar" style="height: 16px; vertical-align: middle;">
                            <span>Sisa Akar</span>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm condition-pill" data-condition="Tumpat" onclick="selectCondition('Tumpat')">
                            <img src="{{ url('images/Tumpat.png') }}" class="pill-icon mr-1" alt="Tumpatan" style="height: 16px; vertical-align: middle;">
                            <span>Tumpatan</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm condition-pill" data-condition="Fraktur Mahkota" onclick="selectCondition('Fraktur Mahkota')">
                            <i class="fas fa-heart-broken mr-1"></i>
                            <span>Fraktur Mahkota</span>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent='save'>
                    <div class="row">
                        <div wire:ignore class="col-md-6">
                            <div class="form-group">
                                <label for="gg_xx" class="font-weight-bold text-sm">
                                    <i class="fas fa-tooth text-secondary mr-1"></i> Pilih Gigi
                                </label>
                                <select name="gg_xx" id="gg_xx" wire:model.defer='gigi' class="form-control formgigi" required>
                                    <option value="">-- Pilih Gigi --</option>
                                    <optgroup label="Gigi Permanen Atas (Maxilla)">
                                        <option value="gg_18">Gigi 18</option>
                                        <option value="gg_17">Gigi 17</option>
                                        <option value="gg_16">Gigi 16</option>
                                        <option value="gg_15">Gigi 15</option>
                                        <option value="gg_14">Gigi 14</option>
                                        <option value="gg_13">Gigi 13</option>
                                        <option value="gg_12">Gigi 12</option>
                                        <option value="gg_11">Gigi 11</option>
                                        <option value="gg_21">Gigi 21</option>
                                        <option value="gg_22">Gigi 22</option>
                                        <option value="gg_23">Gigi 23</option>
                                        <option value="gg_24">Gigi 24</option>
                                        <option value="gg_25">Gigi 25</option>
                                        <option value="gg_26">Gigi 26</option>
                                        <option value="gg_27">Gigi 27</option>
                                        <option value="gg_28">Gigi 28</option>
                                    </optgroup>
                                    <optgroup label="Gigi Desidui Atas (Anak)">
                                        <option value="gg_55">Gigi 55</option>
                                        <option value="gg_54">Gigi 54</option>
                                        <option value="gg_53">Gigi 53</option>
                                        <option value="gg_52">Gigi 52</option>
                                        <option value="gg_51">Gigi 51</option>
                                        <option value="gg_61">Gigi 61</option>
                                        <option value="gg_62">Gigi 62</option>
                                        <option value="gg_63">Gigi 63</option>
                                        <option value="gg_64">Gigi 64</option>
                                        <option value="gg_65">Gigi 65</option>
                                    </optgroup>
                                    <optgroup label="Gigi Desidui Bawah (Anak)">
                                        <option value="gg_85">Gigi 85</option>
                                        <option value="gg_84">Gigi 84</option>
                                        <option value="gg_83">Gigi 83</option>
                                        <option value="gg_82">Gigi 82</option>
                                        <option value="gg_81">Gigi 81</option>
                                        <option value="gg_71">Gigi 71</option>
                                        <option value="gg_72">Gigi 72</option>
                                        <option value="gg_73">Gigi 73</option>
                                        <option value="gg_74">Gigi 74</option>
                                        <option value="gg_75">Gigi 75</option>
                                    </optgroup>
                                    <optgroup label="Gigi Permanen Bawah (Mandibula)">
                                        <option value="gg_48">Gigi 48</option>
                                        <option value="gg_47">Gigi 47</option>
                                        <option value="gg_46">Gigi 46</option>
                                        <option value="gg_45">Gigi 45</option>
                                        <option value="gg_44">Gigi 44</option>
                                        <option value="gg_43">Gigi 43</option>
                                        <option value="gg_42">Gigi 42</option>
                                        <option value="gg_41">Gigi 41</option>
                                        <option value="gg_31">Gigi 31</option>
                                        <option value="gg_32">Gigi 32</option>
                                        <option value="gg_33">Gigi 33</option>
                                        <option value="gg_34">Gigi 34</option>
                                        <option value="gg_35">Gigi 35</option>
                                        <option value="gg_36">Gigi 36</option>
                                        <option value="gg_37">Gigi 37</option>
                                        <option value="gg_38">Gigi 38</option>
                                    </optgroup>
                                </select>
                                <div class="invalid-feedback">
                                    @error('gigi') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div wire:ignore class="form-group">
                                <label for="penyakit" class="font-weight-bold text-sm">
                                    <i class="fas fa-diagnoses text-secondary mr-1"></i> Hasil Pemeriksaan
                                </label>
                                <select class="form-control formpemeriksaan" wire:model.defer='penyakit' id="penyakit" required>
                                    <option value="">-- Pilih Hasil Pemeriksaan --</option>
                                    <option value="Tanggal">Tanggal (Hilang)</option>
                                    <option value="Karies">Karies</option>
                                    <option value="Akar">Sisa Akar</option>
                                    <option value="Tumpat">Tumpatan</option>
                                    <option value="Fraktur Mahkota">Fraktur Mahkota</option>
                                </select>
                                <div class="invalid-feedback">
                                    @error('penyakit') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="catatan" class="font-weight-bold text-sm">
                            <i class="fas fa-sticky-note text-secondary mr-1"></i> Catatan
                        </label>
                        <textarea wire:model.defer="catatan" class="form-control" id="catatan" rows="2" placeholder="Tambah catatan pemeriksaan..."></textarea>
                        @error('catatan') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary px-4 shadow-sm" type="submit">
                            <i class="fas fa-save mr-1"></i> Simpan Pemeriksaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Riwayat Pemeriksaan Odontogram Table -->
        <div class="card card-outline card-secondary shadow-xs">
            <div class="card-header bg-light">
                <h5 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-history text-info mr-2"></i> Riwayat Pemeriksaan Odontogram
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Tanggal & Jam</th>
                                <th>Gigi</th>
                                <th>Hasil Pemeriksaan</th>
                                <th>Catatan</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listPemeriksaanOdontogram as $item)
                                <tr>
                                    <td class="align-middle">
                                        <i class="far fa-clock text-muted mr-1"></i>
                                        {{ $item->tgl_perawatan }} {{ $item->jam_rawat }}
                                    </td>
                                    <td class="align-middle font-weight-bold">
                                        <span class="badge badge-light border px-2 py-1">
                                            <i class="fas fa-tooth text-info mr-1"></i>
                                            {{ str_replace('gg_', 'Gigi ', $item->gg_xx) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        @if($item->value == 'Tanggal')
                                            <span class="badge badge-danger badge-animated px-2 py-1">
                                                <i class="fas fa-times-circle mr-1"></i> Tanggal (Hilang)
                                            </span>
                                        @elseif($item->value == 'Karies')
                                            <span class="badge badge-warning text-dark badge-animated px-2 py-1">
                                                <i class="fas fa-bug mr-1"></i> Karies
                                            </span>
                                        @elseif($item->value == 'Akar')
                                            <span class="badge badge-dark badge-animated px-2 py-1">
                                                <i class="fas fa-spa mr-1"></i> Sisa Akar
                                            </span>
                                        @elseif($item->value == 'Tumpat')
                                            <span class="badge badge-info badge-animated px-2 py-1">
                                                <i class="fas fa-fill-drip mr-1"></i> Tumpatan
                                            </span>
                                        @elseif($item->value == 'Fraktur Mahkota')
                                            <span class="badge badge-secondary badge-animated px-2 py-1">
                                                <i class="fas fa-heart-broken mr-1"></i> Fraktur Mahkota
                                            </span>
                                        @else
                                            <span class="badge badge-primary badge-animated px-2 py-1">
                                                {{ $item->value }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-muted">
                                        {{ $item->catatan ?? '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-outline-danger btn-sm shadow-xs" title="Hapus Data" wire:click="delete('{{ $item->gg_xx }}', '{{ $item->tgl_perawatan }}')">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                                        Belum ada data pemeriksaan odontogram.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
    <style>
        .odontogram-stats-bar .stat-card {
            border-left: 4px solid #17a2b8;
            transition: all 0.25s ease;
        }
        .odontogram-stats-bar .stat-card.border-left-info { border-left-color: #17a2b8 !important; }
        .odontogram-stats-bar .stat-card.border-left-danger { border-left-color: #dc3545 !important; }
        .odontogram-stats-bar .stat-card.border-left-warning { border-left-color: #ffc107 !important; }
        .odontogram-stats-bar .stat-card.border-left-dark { border-left-color: #343a40 !important; }
        .odontogram-stats-bar .stat-card.border-left-success { border-left-color: #28a745 !important; }
        .odontogram-stats-bar .stat-card.border-left-secondary { border-left-color: #6c757d !important; }

        .odontogram-stats-bar .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1) !important;
        }

        .table-odontogram-container {
            width: 100%;
            overflow-x: auto;
            padding: 10px 0;
        }

        .table-odontogram-grid {
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 3px;
            text-align: center;
        }

        .tooth-number-row td {
            font-size: 12px;
            font-weight: bold;
            padding: 2px 0;
        }

        .tooth-cell {
            width: 30px;
            height: 30px;
            min-width: 30px;
            min-height: 30px;
            position: relative;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            vertical-align: middle;
            background-color: #fff;
        }

        .gigi_posterior {
            background-image: url("{{ url('images/posterior.png') }}");
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .gigi_anterior {
            background-image: url("{{ url('images/anterior.png') }}");
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .tooth-cell:hover {
            transform: scale(1.25);
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            outline: 2px solid #17a2b8;
        }

        .tooth-cell.tooth-selected {
            animation: pulse-ring 1.5s infinite;
            outline: 2px solid #28a745;
            z-index: 5;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        .tooth-img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            margin: 0 auto;
            pointer-events: none;
        }

        .condition-pill {
            transition: all 0.2s ease-in-out;
            border-radius: 20px;
            font-weight: 500;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .condition-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
        }

        .condition-pill.active {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.5);
            font-weight: bold;
        }

        .condition-pill[data-condition="Tanggal"].active {
            background-color: #dc3545;
            color: #fff !important;
            border-color: #dc3545;
        }

        .condition-pill[data-condition="Karies"].active {
            background-color: #ffc107;
            color: #212529 !important;
            border-color: #ffc107;
        }

        .condition-pill[data-condition="Akar"].active {
            background-color: #343a40;
            color: #fff !important;
            border-color: #343a40;
        }

        .condition-pill[data-condition="Tumpat"].active {
            background-color: #17a2b8;
            color: #fff !important;
            border-color: #17a2b8;
        }

        .condition-pill[data-condition="Fraktur Mahkota"].active {
            background-color: #6c757d;
            color: #fff !important;
            border-color: #6c757d;
        }

        .badge-animated {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .badge-animated:hover {
            transform: scale(1.08);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        *:focus {
            outline: 0px;
        }
    </style>
@endpush

@push('js')
    <script>
        $(document.body).ready(function() {
            initOdontogramSelect2();
        });

        function initOdontogramSelect2() {
            $('.formgigi').select2({
                placeholder: 'Pilih Gigi',
                width: '100%'
            });
            
            $('.formpemeriksaan').select2({
                placeholder: 'Pilih Hasil Pemeriksaan',
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        function selectTooth(toothId, label) {
            $('.formgigi').val(toothId).trigger('change');
            updateToothSelectionUI(toothId);
        }

        function selectCondition(value) {
            $('.formpemeriksaan').val(value).trigger('change');
            updateConditionPillUI(value);
        }

        function updateToothSelectionUI(toothId) {
            $('.tooth-cell').removeClass('tooth-selected');
            if (toothId) {
                $('[data-tooth="' + toothId + '"]').addClass('tooth-selected');
            }
        }

        function updateConditionPillUI(value) {
            $('.condition-pill').removeClass('active');
            if (value) {
                $('[data-condition="' + value + '"]').addClass('active');
            }
        }

        $('.formgigi').on('change', function (e) {
            let data = $(this).val();
            @this.set('gigi', data);
            updateToothSelectionUI(data);
        });

        $('.formpemeriksaan').on('change', function (e) {
            let data = $(this).val();
            @this.set('penyakit', data);
            updateConditionPillUI(data);
        });

        window.addEventListener('swal:odontogram', function(e){
            Swal.fire(e.detail);
        });

        window.addEventListener('swal:odontogram:confirm', function(e){
            Swal.fire({
                title: e.detail.title,
                text: e.detail.text,
                icon: e.detail.icon,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: e.detail.confirmButtonText,
                cancelButtonText: e.detail.cancelButtonText,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.livewire.emit(e.detail.function, e.detail.params[0]);
                }
            });
        });

        window.addEventListener('odontogram:saved', function() {
            $('.formgigi').val('').trigger('change');
            $('.formpemeriksaan').val('').trigger('change');
            $('.tooth-cell').removeClass('tooth-selected');
            $('.condition-pill').removeClass('active');
        });

        window.livewire.on('select2', () => {
            initOdontogramSelect2();
        });
    </script>
@endpush
