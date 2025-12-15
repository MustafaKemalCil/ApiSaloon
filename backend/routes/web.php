    <?php
    use App\Models\User;

    use App\Http\Controllers\Web\AuthController;
    use App\Http\Controllers\Web\DashboardController;
    use App\Http\Controllers\Web\TestController;
    use App\Http\Controllers\Web\ProfileController;
    use App\Http\Controllers\Web\CustomerController;
    use App\Http\Controllers\Web\AppointmentController;
    use App\Http\Controllers\Web\EmployeeController;
    use Illuminate\Support\Facades\Route;

    // Ana Sayfa
    Route::get('/', function () {
        return view('pages.home');
    })->name('home');

    // Hakkımızda
    Route::get('/about', function () {
        return view('pages.about');
    })->name('about');

    // İletişim
    Route::get('/contact', function () {
        return view('pages.contact');
    })->name('contact');
    // Auth sayfaları
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    // kayıt
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Korunan dashboard sayfası
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('auth')
        ->name('dashboard');
    // Profil sayfası
    Route::get('/profile', [ProfileController::class, 'index'])
        ->middleware('auth')
        ->name('profile');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // Müşteri işlemleri
    Route::prefix('customers')->middleware('auth')->group(function () {
        // Müşteri listeleme
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');

        // Yeni müşteri formu
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');

        // Müşteri ekleme
        Route::post('/store', [CustomerController::class, 'store'])->name('customers.store');

        // Müşteri düzenleme formu
        Route::get('/edit/{customer}', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('check.manager');

        // Müşteri güncelleme
        Route::put('/edit/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('check.manager');

        // Müşteri silme
        Route::delete('/delete/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('check.admin');
    });


    //randevu
    Route::get('/appointments', [AppointmentController::class, 'index'])
        ->middleware('auth')
        ->name('appointments.index');

    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->middleware('auth')
        ->name('appointments.store');


    //calısanlar
    Route::prefix('employees')
        ->middleware('auth')
        ->name('employees.') // route isimlerini otomatik prefixler
        ->group(function () {

            // Çalışanları listeleme
            Route::get('/', [EmployeeController::class, 'index'])->name('index');

            // Yeni çalışan ekleme
            Route::post('/', [EmployeeController::class, 'store'])->name('store')->middleware('check.manager');

            // Çalışan güncelleme
            Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update')->middleware('check.admin');

            // Çalışan silme
            Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy')->middleware('check.admin');
    });

    //profil sayfası
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password-update', [ProfileController::class, 'updatePassword'])
        ->name('profile.updatePassword');
