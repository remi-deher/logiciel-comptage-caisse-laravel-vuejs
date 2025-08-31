use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CalculateurController;

// Route pour charger les données initiales (caisses, terminaux, etc.)
Route::get('/calculateur/init', [CalculateurController::class, 'init']);

// Route pour sauvegarder un comptage
Route::post('/calculateur/save', [CalculateurController::class, 'save']);
