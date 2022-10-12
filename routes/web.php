<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();
Route::get('/', [App\Http\Controllers\LoginController::class, 'index'])->name('login');
Route::post('/customlogin', [App\Http\Controllers\LoginController::class, 'customLogin'])->name('customlogin');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/ralan/pasien', [App\Http\Controllers\Ralan\PasienRalanController::class, 'index'])->name('ralan.pasien');
Route::get('/ralan/pemeriksaan', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'index'])->name('ralan.pemeriksaan');
Route::get('/ralan/obat', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'getObat'])->name('ralan.obat');
Route::post('/ralan/simpan/resep/{noRawat}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'postResep'])->name('ralan.simpan.resep');
Route::post('/ralan/simpan/racikan/{noRawat}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'postResepRacikan'])->name('ralan.simpan.racikan');
Route::post('/ralan/simpan/copyresep/{noRawat}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'postCopyResep'])->name('ralan.simpan.copyresep');
Route::post('/ralan/simpan/resumemedis/{noRawat}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'postResumMedis']);
Route::delete('/ralan/obat/{noResep}/{kdObat}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'hapusObat']);
Route::delete('/ralan/racikan/{noResep}/{noRacik}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'hapusObatRacikan']);
Route::get('/ralan/copy/{noResep}', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'getCopyResep']);
Route::post('/ralan/pemeriksaan/submit', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'postPemeriksaan'])->name('ralan.pemeriksaan.submit');
Route::post('/ralan/catatan/submit', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'postCatatan'])->name('ralan.catatan.submit');
Route::get('/logout', [App\Http\Controllers\HomeController::class, 'logout'])->name('logout');

Route::get('/ranap/pasien', [App\Http\Controllers\Ranap\PasienRanapController::class, 'index'])->name('ranap.pasien');
Route::get('/ranap/pemeriksaan', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'index'])->name('ranap.pemeriksaan');
Route::post('/ranap/pemeriksaan/submit', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'postPemeriksaan'])->name('ranap.pemeriksaan.submit');
Route::get('/ranap/copy/{noResep}', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'getCopyResep']);
Route::get('/ranap/pemeriksaan/{noRawat}/{tgl}/{jam}', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'getPemeriksaan']);
Route::post('/ranap/pemeriksaan/edit/{noRawat}/{tgl}/{jam}', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'editPemeriksaan']);
Route::get('/ranap/obat', [App\Http\Controllers\Ralan\PemeriksaanRalanController::class, 'getObat'])->name('ranap.obat');
Route::post('/ranap/simpan/resep/{noRawat}', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'postResep'])->name('ranap.simpan.resep');
Route::delete('/ranap/obat/{noResep}/{kdObat}', [App\Http\Controllers\Ranap\PemeriksaanRanapController::class, 'hapusObat']);

Route::get('/master_obat', [App\Http\Controllers\MasterObat::class, 'index'])->name('master_obat');



