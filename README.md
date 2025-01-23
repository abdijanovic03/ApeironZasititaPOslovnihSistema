Naravno! Evo kompletne dokumentacije za tvoj projekat, u formatu koji je pogodan za README fajl, sa svim objašnjenjima i uputstvima. Dodao sam mesta za screenshotove i detaljno objasnio implementaciju rate limiting-a, OAuth2 autentifikacije i Swagger dokumentacije.

---

# **ApeironZasititaPOslovnihSistema**

## **Opis projekta**

Projekat **ApeironZasititaPOslovnihSistema** implementira zaštitu poslovnih sistema u Laravelu koristeći tehnologije kao što su **rate limiting**, **OAuth2** autentifikaciju putem **Passport** i generisanje **Swagger** dokumentacije za API.

### **Ključne tehnologije**:
- Laravel
- Passport za OAuth2 autentifikaciju
- Swagger za automatsku dokumentaciju API-ja
- Rate Limiting za zaštitu od preopterećenja

---

## **1. Rate Limiting Implementacija u Laravelu**

### **Šta je rate limiting?**

**Rate limiting** je zaštitna tehnika koja omogućava kontrolu broja zahteva prema serveru u određenom vremenskom intervalu. U Laravelu je implementacija rate limiting-a jednostavna i može se konfigurirati putem **middleware-a**.

### **Kako je implementiran rate limiter u Laravelu?**

1. **Default Rate Limiting**:
   Laravel dolazi sa **default rate limiter-om** koji se može jednostavno konfigurirati u **`RouteServiceProvider`** ili direktno u kontrolerima. Laravel koristi `ThrottleRequests` middleware da bi zaštitio API rute od preopterećenja.

2. **Konfiguracija rate limiter-a**:

   U projektu, rate limiter je konfigurisan u fajlu **`RouteServiceProvider.php`**.

   ```php
   protected function configureRateLimiting(): void
   {
       // Ograničava broj zahteva za login i registraciju na 5 po minuti
       RateLimiter::for('auth', function (Request $request) {
           return Limit::perMinute(5)->response(function () {
               return response()->json(
                   ['Too many requests. Try again later.', 429],
                   429
               );
           });
       });

       // Ograničava broj zahteva za API rute na 10 po minuti
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

### **Objašnjenje**:

- **`RateLimiter::for('auth', ...)`**:
   - Ovaj limiter se koristi za rute **registracije** i **login**.
   - Ograničava broj zahteva na **5 zahteva po minuti**.
   - Ako korisnik premaši ovaj broj, server će vratiti odgovor sa status kodom **429**: `Too many requests. Try again later.`.

- **`RateLimiter::for('api', ...)`**:
   - Ovaj limiter se koristi za sve **API rute** nakon što korisnik bude autentifikovan.
   - Ograničava broj zahteva na **10 zahteva po minuti**.
   - Ako korisnik premaši ovaj broj, server će vratiti odgovor sa status kodom **429**: `Too many requests. Try again later.`.

### **Korišćenje rate limiting-a u rutama**

Nakon što je rate limiting konfigurisan, koristiš ga u rutama kroz middleware:

```php
Route::post('user/register', [ApiController::class, 'register'])->middleware('throttle:auth');
Route::post('user/login', [ApiController::class, 'login'])->middleware('throttle:auth');

Route::group([
    'middleware' => ['auth:api', 'throttle:api'],
], function () {

    Route::get('user/me', [ApiController::class, 'profile']);
    Route::get('user/refresh-token', [ApiController::class, 'refreshToken']);
    Route::get('user/logout', [ApiController::class, 'logout']);

    Route::post('blog', action: [BlogController::class, 'createBlog']);
    Route::get('blog', [BlogController::class, 'listBlog']);
    Route::get('blog/me', [BlogController::class, 'myBlog']);
    Route::get('blog/{id}', [BlogController::class, 'getById']);
    Route::put('blog', [BlogController::class, 'updateBlog']);
    Route::delete('blog/{id}', [BlogController::class, 'deleteBlog']);
});
```

### **Objašnjenje**:

- **`Route::post('user/register', ...)`** i **`Route::post('user/login', ...)`**:
   - Ove rute koriste **middleware `throttle:auth`**, koji se odnosi na rate limiter za autentifikaciju. To znači da će korisnici moći da pošalju najviše **5 zahteva po minuti** za registraciju i login.
   
- **`Route::group(['middleware' => ['auth:api', 'throttle:api'], ...])`**:
   - Ovaj **middleware** grupiše rute koje su zaštićene autentifikacijom (`auth:api`) i rate limitingom (`throttle:api`).
   - API rute unutar ove grupe (kao što su `user/me`, `blog`, itd.) omogućavaju korisnicima da izvrše najviše **10 zahteva po minuti** nakon što se autentifikuju.

---

## **2. Swagger Dokumentacija u Laravelu**

### **Šta je Swagger?**

**Swagger** omogućava automatsko generisanje interaktivne dokumentacije za API. Sa Swagger UI-om možeš testirati API direktno iz browser-a.

### **Kako implementirati Swagger u Laravel?**

1. **Instalacija potrebnih paketa**:

   Da bi koristiš Swagger u Laravelu, koristi **`darkaonline/l5-swagger`** paket:

   ```bash
   composer require darkaonline/l5-swagger
   ```

2. **Objavljivanje konfiguracije Swagger-a**:

   Objavi konfiguraciju:
   ```bash
   php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
   ```

3. **Generisanje Swagger dokumentacije**:

   Swagger dokumentacija može se generisati automatski. Dodaj anotacije u tvoje rute i kontrolere:

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
   Route::middleware('auth:api')->get('/user', function (Request $request) {
       return $request->user();
   });
   ```

4. **Pristup Swagger dokumentaciji**:

   Nakon što je sve podešeno, Swagger dokumentacija će biti dostupna na:
   ```bash
   http://localhost:8000/api/documentation
   ```

---

## **3. OAuth2 Implementacija sa Laravel Passport**

### **Šta je OAuth2?**

**OAuth2** je industrijski standard za autentifikaciju i autorizaciju korisnika. Korišćenjem **Passport** paketa u Laravelu, možemo jednostavno implementirati OAuth2 autentifikaciju.

### **Kako implementirati OAuth2 sa Laravel Passport?**

1. **Instalacija Laravel Passport-a**:
   
   Da bi koristiš **Passport** za OAuth2 autentifikaciju, prvo moraš instalirati paket:
   ```bash
   composer require laravel/passport
   ```

2. **Pokretanje Passport migracija**:
   
   Passport dolazi sa potrebnim migracijama za kreiranje tabela u bazi podataka. Pokreni migracije komandom:
   ```bash
   php artisan migrate
   ```

3. **Registrovanje Passport servisa**:

   Nakon instalacije, registruj Passport servis u `config/app.php`:

   ```php
   'providers' => [
       ...
       Laravel\Passport\PassportServiceProvider::class,
   ],
   ```

4. **Objavljivanje konfiguracije Passport-a**:
   
   Objavi konfiguraciju Passport-a:
   ```bash
   php artisan passport:install
   ```

5. **Kreiranje AuthController-a i ruta**:

   Nakon što je Passport postavljen, kreiraj rutu za autentifikaciju putem OAuth2:

   ```php
   Route::post('login', 'AuthController@login');
   Route::post('register', 'AuthController@register');
   Route::middleware('auth:api')->get('/user', function (Request $request) {
       return $request->user();
   });
   ```

---

## **4. Kako koristiti projekat**

1. Kloniraj projekat:
   ```bash
   git clone https://github.com/abdijanovic03/ApeironZasititaPOslovnihSistema.git
   ```

2. Instaliraj zavisnosti:
   ```bash
   composer install
   npm install
   ```

3. Pokreni aplikaciju:
   ```bash
   php artisan serve
   ```

4. Poseti Swagger dokumentaciju na `http://localhost:8000/api/documentation`.

---

## **Zaključak**

Ovaj projekat koristi Laravel za implementaciju **rate limiting-a**, **OAuth2 autentifikacije** preko **Passport-a** i generisanje **Swagger** dokumentacije. Ove komponente poboljšavaju sigurnost i omogućavaju lako testiranje i integraciju sa drugim sistemima.

---

**Napomena**: Dodaj odgovarajuće screenshotove u dokumentaciju na mesta označena sa **(Slika ovde)** kako bi prikazao rad sistema u praksi.

