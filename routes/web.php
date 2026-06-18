<?php


use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankingController;
use App\Http\Middleware\SessionAuthenticate;
use App\Http\Middleware\TokenVerificationMiddleware;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    // return view('welcome');
    return Inertia::render('TestPage');
});

Route::get('/', [HomeController::class, 'index'])->name('home.Page');
Route::get('/Home', [HomeController::class, 'HomePage'])->name('Home');


// All user Routes
Route::post('/user-registration', [UserController::class, 'UserRegistration'])->name('UserRegistration');
Route::post('/user-login', [UserController::class, 'UserLogin'])->name('user.login');
// Route::get('/user-logout', [UserController::class, 'UserLogout'])->name('user.logout');
Route::get('/dashboardPage', [DashboardController::class, 'DashboardPage'])->middleware([SessionAuthenticate::class])->name('dashboard.page');

//send otp
Route::post('/send-otp', [UserController::class, 'SendOtpCode'])->name('send.otp');
Route::post('/verify-otp', [UserController::class, 'VerifyOtp'])->name('verify.otp');
Route::post('/reset-password', [UserController::class, 'ResetPassword'])->middleware([SessionAuthenticate::class]);

// Make Route group
Route::middleware(SessionAuthenticate::class)->group(function () {


    // Product all routes
    Route::controller(ProductController::class)->group(function () {
        Route::post('/create-product', 'CreateProduct')->name('product.create');
        Route::get('/product-list', 'ProductList')->name('product.list');
        Route::post('/product-detail-by-id', 'ProductDetail')->name('product.detail.by.id');
        Route::post('/product-update', 'ProductUpdate')->name('product.update');
        Route::get('/product-delete/{id}', 'ProductDelete')->name('product.delete');
        Route::post('/product-adjust-stock', 'AdjustStock')->name('product.adjust.stock');
        Route::get('/product-inventory-logs', 'InventoryLogs')->name('product.inventory.logs');

        Route::get('/ProductPage', 'ProductPage')->name('productPage');
        Route::get('/ProductSavePage', 'productSavePage')->name('productSavePage');

    });

    // Customer all routes
    Route::controller(CustomerController::class)->group(function () {
        Route::post('/create-customer', 'CreateCustomer')->name('customer.create');
        Route::get('/customer-list', 'CustomerList')->name('customer.list');
        Route::post('/customer-detail-by-id', 'CustomerDetail')->name('customer.detail.by.id');
        Route::post('/customer-update', 'CustomerUpdate')->name('customer.update');
        Route::get('/customer-delete/{id}', 'CustomerDelete')->name('customer.delete');
        Route::get('/customer-invoice-report', 'CustomerInvoiceReport')->name('customer.invoice.report');

        Route::get('/CustomerPage', 'CustomerPage')->name('CustomerPage');
        Route::get('/CustomerSavePage', 'CustomerSavePage')->name('CustomerSavePage');

    });

    // Invoice all routes
    Route::controller(InvoiceController::class)->group(function () {
        Route::post('/create-invoice', 'CreateInvoice')->name('create.invoice');
        Route::post('/update-invoice', 'UpdateInvoice')->name('invoice.update');
        Route::post('/update-invoice-status', 'UpdateInvoiceStatus')->name('invoice.status');
        Route::post('/invoice-create-delivery-challan', 'CreateDeliveryChallan')->name('invoice.challan');
        Route::get('/invoice-list', 'InvoiceList')->name('invoice.list');
        Route::post('/invoice-details', 'InvoiceDetails')->name('invoice.details');
        Route::get('/invoice-delete/{id}', 'InvoiceDelete')->name('invoice.delete');

        Route::get('/InvoiceListPage', 'InvoiceListPage')->name('InvoicePage');
        Route::get('/InvoiceSavePage', 'InvoiceSavePage')->name('InvoiceSavePage');
    });

    // Banking all routes
    Route::controller(BankingController::class)->group(function () {
        Route::get('/BankingPage', 'BankingPage')->name('BankingPage');
        Route::post('/add-bank-account', 'AddBankAccount')->name('bank.account.add');
        Route::post('/add-bank-transaction', 'AddTransaction')->name('bank.transaction.add');
    });

    // Dashboard Summary
    Route::get('/dashboard-summary', [DashboardController::class, 'DashboardController'])->name('dashboard.summary');

    // Profile update
    Route::post('/user-profile-update', [UserController::class, 'UserUpdate'])->name('dashboard.summary');
    Route::get('/userProfilePage', [UserController::class, 'UserProfilePage'])->name('ProfilePage');
    Route::get('/user-logout', [UserController::class, 'UserLogout'])->name('user.logout');


    //sale all routes
    Route::controller(SaleController::class)->group(function () {
        Route::get('/create-sale', 'SalePage')->name('sale.page');
        Route::post('/create-quotation', 'CreateQuotation')->name('quotation.create');
        Route::post('/update-quotation', 'UpdateQuotation')->name('quotation.update');
        Route::get('/delete-quotation/{id}', 'DeleteQuotation')->name('quotation.delete');
        Route::post('/update-quotation-status', 'UpdateQuotationStatus')->name('quotation.status');
        Route::post('/convert-to-invoice', 'ConvertToInvoice')->name('quotation.convert_invoice');
    });

    // Deliveries all routes
    Route::controller(DeliveryController::class)->group(function () {
        Route::get('/DeliveriesPage', 'DeliveriesPage')->name('deliveries.page');
        Route::post('/create-delivery-challan', 'CreateDeliveryChallan')->name('challan.create');
        Route::post('/update-delivery-challan', 'UpdateDeliveryChallan')->name('challan.update');
        Route::get('/delete-delivery-challan/{id}', 'DeleteDeliveryChallan')->name('challan.delete');
        Route::post('/mark-challan-delivered', 'MarkChallanDelivered')->name('challan.deliver');
    });

    // Expenses all routes
    Route::controller(App\Http\Controllers\ExpenseController::class)->group(function () {
        Route::get('/ExpensesPage', 'ExpensesPage')->name('expenses.page');
        Route::post('/add-expense', 'AddExpense')->name('expense.add');
    });

});

// Front end all route
Route::get('/login', [UserController::class, 'LoginPage'])->name('login.page');
Route::get('/registration', [UserController::class, 'RegistrationPage'])->name('RegistrationPage');
Route::get('/send-otp', [UserController::class, 'SendOtpPage'])->name('SendOtpPage');
Route::get('/verify-otp', [UserController::class, 'VerifyOtpPage'])->name('VerifyOtpPage');
Route::get('/reset-password', [UserController::class, 'ResetPasswordPage'])->name('ResetPasswordPage')->middleware([SessionAuthenticate::class]);

