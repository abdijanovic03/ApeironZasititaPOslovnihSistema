## ApeironZasititaPoslovnihSistema
Opis projekta
Projekat Zaštita web aplikacije uz pomoć Laravel-a implementira zaštitu poslovnih sistema uz pomoć tehnologija kao što su rate limiting, OAuth2 autentifikaciju putem Passport i generisanje Swagger dokumentacije za API-a.

Ključne tehnologije:
- Laravel
- Passport za OAuth2 autentifikaciju
- Swagger za automatsku dokumentaciju API-ja
- Rate Limiting za zaštitu od preopterećenja
  
## Rate Limiting Implementacija u Laravelu
Šta je rate limiting?
Rate limiting je zaštitna tehnika koja omogućava kontrolu broja zahtjeeva prema serveru u određenom vremenskom intervalu. U Laravelu je implementacija rate limiting-a relativno jednostavna i može se konfigurirati putem middleware-a.

### Kako je implementiran rate limiter u Laravelu?
Dodavanjem novog provider-a u Laravel aplikaciju pod nazivom RouteServiceProvider, omogućujemo visoku kontrolu nad našim API pozivima, unutar ovog fajla smo definisali dva rate limiter-a, jedan za registraciju/login, što su javne rute koje bilo ko može gađati i tako ometati rad sistema, nju smo nazvali auth, te drugi rate limiter, api, koji je namjenjen za sve ostale rute koje su pod dodatnom zaštitom 0Auth2.0 sistema.

### Objašnjenje:
```RouteServiceProvider```

```php
protected function configureRateLimiting(): void
{
    // Ograničava broj zahtjeva za login i registraciju na 5 po minuti
    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->response(function () {
            return response()->json(
                ['Too many requests. Try again later.', 429],
                429
            );
        });
    });

    // Ograničava broj zahtjeva za API rute na 10 po minuti
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(10)->response(function () {
            return response()->json(
                ['Too many requests. Try again later.', 429],
                429
            );
        });
    });
}
```
- RateLimiter::for('auth', ...) i RateLimiter::for('api', ...) vraćaju poruku sa status kodom : 429: Too many requests. Try again later..
- RateLimiter::for('auth', ...) ograničava broj zahtjeva na 5 po minuti.
- RateLimiter::for('api', ...) ograničava broj zahtjeva na 10 po minuti.


### Primjer korištenje rate limiting-a u rutama
- Nakon što je rate limiting konfigurisan, koristimo ga u rutama kroz middleware:

```php
Route::post('user/register', [ApiController::class, 'register'])->middleware('throttle:auth');

Route::group([
    'middleware' => ['auth:api', 'throttle:api'],
], function () {
    Route::get('user/me', [ApiController::class, 'profile']);
    Route::get('user/refresh-token', [ApiController::class, 'refreshToken']);
    Route::get('user/logout', [ApiController::class, 'logout']);
    Route::post('blog', action: [BlogController::class, 'createBlog']);
    Route::get('blog', [BlogController::class, 'listBlog']);
Route::group(['middleware' => ['auth:api', 'throttle:api'], ...]):
```

### Ovaj middleware grupiše rute koje su zaštićene autentifikacijom (auth:api) i rate limitingom (throttle:api).

## Swagger Dokumentacija u Laravelu

- Swagger omogućava automatsko generisanje interaktivne dokumentacije za API. Sa Swagger UI-om možemo testirati API direktno iz browser-a sa predefinisanim request podatcima i response code-ovima.

### Kako implementirati Swagger u Laravel?
- Instalacija potrebnih paketa:

Da bi koristili Swagger u Laravelu, koristili smo darkaonline/l5-swagger paket:
```bash
composer require darkaonline/l5-swagger
```
Publish konfiguracije Swagger-a:

### Publish konfiguracije:

```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

### Generisanje Swagger dokumentacije:

Swagger dokumentacija može se generisati uz pomoć anotacija u kontrolerima iznad ruta.:
```php
/**
 * @OA\Get(
 *     path="/api/user",
 *     summary="Return current user",
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *     ),
 * )
 */
```
Da bi osigurali da se dokumentacija generiše, moramo pokrenuti ovu komandu

```bash
php artisan l5-swagger:generate
```

### Pristup Swagger dokumentaciji:

Nakon što je sve podešeno, Swagger dokumentacija će biti dostupna na:
```
http://localhost:8000/api/documentation
```

## OAuth2 Implementacija sa Laravel Passport

OAuth2 je industrijski standard za autentifikaciju i autorizaciju korisnika.0Auth2 omogućava dobro korisničko iskustvo i sigurnost sitema kao i mogućnost povezivanja eksternih sistema na našu aplikaciju što je u većini slučajeva i više nego dovoljno za jednu web aplikaciju. Korištenjem Passport paketa u Laravelu, možemo efikasno i sigurno implementirati OAuth2 autentifikaciju.

### Kako implementirati OAuth2 sa Laravel Passport?
Instalacija Laravel Passport-a:
```bash
composer require laravel/passport
```

### Pokretanje Passport migracija:

- Passport dolazi sa potrebnim migracijama za kreiranje tabela u bazi podataka. Pokreni migracije komandom:
```bash
php artisan migrate
```

### Publish konfiguracije Passport-a:

```bash
php artisan passport:install
```

### Kreiranje AuthController-a i ruta:

Nakon toga, u config/auth.php, unutar sekcije guards, dodajemo novi guard kao u primjeru ispod
```php
'api' => [ 'driver' => 'passport', 'provider' => 'users', ]
```

Nakon toga, da bi osigurali da su naše rute žaštićenje, dodajemo grupu ruta u routes/api.php file sa middleware-om

```php
 Route::group([
     'middleware' => ['auth:api', 'throttle:api'],
 ]
```

Sa ovim smo omogućili 0Auth2 u našoj aplikaciji.

## Zaključak
Ovaj projekat koristi Laravel za implementaciju rate limiting-a, OAuth2 autentifikacije preko Passport-a i generisanje Swagger dokumentacije. Ove komponente poboljšavaju sigurnost i omogućavaju lako testiranje i integraciju sa drugim sistemima. Ova dokumentacija je kratki prikaz setup-a i koda koji je potreban da postigne ova funkcionalnost.

## Setup projekta

Napomena: u repozitoriju se nalazi docker-compose.yml i dockerfile koji su potrebni za korištenje ovog sistema,
- Aplikacija se može koristiti i bez ovih servisa, u takvom slučaju nam treba Laravel instalacija lokalno i postgresql server za bazu, ova metoda nije preporucena te .env.example fajl nije postavljen prema tim kriterijama
- Ukoliko ste na windows sistemu, nakon setup-a docker desktopa, moguće su loše performanse u swagger dokumenatciji ukoliko nemate namješten WSL, druga opcija po kojoj je .env.example fajl postavljen je da koristite lokalnun laravel konfiguraciju i baze kroz docker.
- Za linux korisnike, kloniranje i setup docker-a i env fajla je sve što treba da se sistem pokrene i radi efikasno.

### Kloniraj repozitoriji:

```
git clone https://github.com/abdijanovic03/ApeironZasititaPOslovnihSistema.git
```

### Pokreni docker container-e(opcionalno):

```bash
docker compose up -d --build
```

### Instaliraj zavisnosti:

```bash
composer install
```

### Pokreni migracije za bazu:
```bash
php artisan migrate
```

### Kreiraj potrebne ključeve:
```bash
php artisan passport:client --personal
php artisan key:generate
```

Pokreni aplikaciju:
```bash
php artisan serve
```

Posjeti Swagger dokumentaciju na http://localhost:8000/api/documentation.

Nakon registracije i logiranja, token koji dobijete postavite u swagger u gornjem desnom uglu.
