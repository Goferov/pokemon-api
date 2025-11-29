# Pokemon API

API serwis w Laravelu służący do pobierania informacji o Pokemonach z PokeAPI, zarządzania rejestrem zakazanych Pokemonów oraz tworzenia i obsługi własnych Pokemonów.

- Dane oficjalnych Pokemonów są pobierane z PokeAPI i cache’owane do najbliższej godziny 12:00 (strefa `Europe/Warsaw`).
- Rejestr zakazanych Pokemonów blokuje zwracanie danych o wybranych nazwach.
- Własne Pokemony są przechowywane w bazie danych i również zwracane z endpointu `/info` z flagą `is_custom = true`.
- Endpointy administracyjne (`/banned`, `/custom-pokemons`) są zabezpieczone nagłówkiem `X-SUPER-SECRET-KEY` (należy ustawić go w .env).

---

## 1. Wymagania

- PHP >= 8.1 (zalecane 8.2+)
- Composer
- Baza danych (np. PostgreSQL / MySQL / SQLite)
- Rozsądnie jest mieć dostęp do internetu (komunikacja z PokeAPI)

---

## 2. Instalacja i uruchomienie

Poniżej kroki od sklonowania repozytorium do działającego serwera.

1. **Sklonuj repozytorium**

   ```bash
   git clone https://github.com/Goferov/pokemon-api.git
   cd pokemon-api
   ```

2. **Zainstaluj zależności PHP**

   ```bash
   composer install
   ```

3. **Skonfiguruj plik `.env`**

   Skopiuj plik przykładowy i dostosuj ustawienia:

   ```bash
   cp .env.example .env
   ```

   W pliku `.env` ustaw co najmniej:

   ```env
   APP_NAME=PokemonApi
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost

   # Baza danych (przykład dla PostgreSQL)
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=pokemon
   DB_USERNAME=pokemon
   DB_PASSWORD=pokemon

   # PokeAPI
   POKEAPI_BASE_URL=https://pokeapi.co/api/v2

   # Klucz do autoryzacji endpointów administracyjnych
   SUPER_SECRET_KEY=super-secret-value

   # Strefa czasu (do cache)
   APP_TIMEZONE=Europe/Warsaw
   ```

4. **Wygeneruj klucz aplikacji**

   ```bash
   php artisan key:generate
   ```

5. **Uruchom migracje**

   ```bash
   php artisan migrate
   ```

6. **Uruchom serwer HTTP**

   ```bash
   php artisan serve
   ```

   Domyślnie API będzie dostępne pod adresem:

    - `http://localhost:8000/api`

---

## 3. Autoryzacja

Dla endpointów administracyjnych wymagany jest nagłówek:

```http
X-SUPER-SECRET-KEY: <wartość_z_env_SUPER_SECRET_KEY>
```

Jeśli nagłówek jest:

- **brakujący** – zwracany jest status `401 Unauthorized`,
- **niepoprawny** – zwracany jest status `403 Forbidden`.

Endpoint `/api/info` jest **publiczny** (nie wymaga nagłówka).

---

## 4. Konwencje odpowiedzi i walidacji

- Dla błędów walidacji zwracany jest standardowy JSON Laravelowy z kodem `422 Unprocessable Entity`, np.:

  ```json
  {
    "message": "The given data was invalid.",
    "errors": {
      "name": [
        "The name field is required."
      ]
    }
  }
  ```

- Wszystkie odpowiedzi są w JSON.

---

## 5. Endpointy

Wszystkie ścieżki poniżej są liczone względem prefiksu `/api`, tzn. `POST /info` oznacza realnie `POST /api/info`.

### 5.1. `POST /info` – Informacje o pokemonach

**Opis:**  
Zwraca informacje o wybranych pokemonach na podstawie listy nazw.
- Uwzględnia zarówno oficjalne Pokemony z PokeAPI, jak i własne (customowe) z bazy.
- Pokemony zakazane (z `/banned`) są ignorowane i nie są zwracane.
- Każdy wynik ma flagę `is_custom`:
    - `false` – pokemon z PokeAPI,
    - `true` – pokemon dodany własnoręcznie.

**Autoryzacja:** brak (publiczny).

**Body (JSON):**

```json
{
  "names": ["pikachu", "charizard", "moja-nazwa"]
}
```

- `names` – **wymagane**, tablica stringów, min. 1 element.

**Odpowiedź `200 OK`:**

```json
[
  {
    "name": "pikachu",
    "height": 4,
    "weight": 60,
    "types": ["electric"],
    "is_custom": false
  },
  {
    "name": "moja-nazwa",
    "height": 10,
    "weight": 100,
    "types": ["fire"],
    "is_custom": true
  }
]
```

---

### 5.2. `/banned` – Rejestr zakazanych Pokemonów

**Prefiks:** `/api/banned`  
**Autoryzacja:** wymagany nagłówek `X-SUPER-SECRET-KEY`

#### 5.2.1. `GET /banned` – lista zakazanych

Zwraca listę wszystkich zakazanych Pokemonów.

**Odpowiedź `200 OK`:**

```json
[
  {
    "id": 1,
    "name": "mewtwo",
    "created_at": "2025-01-01T12:00:00.000000Z"
  },
  {
    "id": 2,
    "name": "charizard",
    "created_at": "2025-01-02T08:30:00.000000Z"
  }
]
```

#### 5.2.2. `POST /banned` – dodanie zakazanego Pokemona

Dodaje nazwę Pokemona do rejestru zakazanych. Nazwa jest przechowywana w lowercase.

**Body (JSON):**

```json
{
  "name": "mewtwo"
}
```

- `name` – **wymagane**, string.

**Odpowiedź `201 Created`:**

```json
{
  "id": 3,
  "name": "mewtwo",
  "created_at": "2025-01-03T10:00:00.000000Z"
}
```

#### 5.2.3. `DELETE /banned/{id}` – usunięcie z rejestru

Usuwa zakazanego Pokemona na podstawie jego `id`.

**Parametry ścieżki:**

- `id` (w kodzie `bannedPokemon`) – integer, identyfikator w tabeli `banned_pokemons`.

**Odpowiedzi:**

- `204 No Content` – jeśli rekord został usunięty,
- `404 Not Found` – jeśli nie znaleziono takiego rekordu (obsługiwane przez route model binding Laravela).

---

### 5.3. `/custom-pokemons` – Własne Pokemony

**Prefiks:** `/api/custom-pokemons`  
**Autoryzacja:** wymagany nagłówek `X-SUPER-SECRET-KEY`  
**Kontroler:** `CustomPokemonController` (zdefiniowany przez `Route::apiResource`)

Wszystkie rekordy są przechowywane w tabeli `custom_pokemons`. Nazwy pokemonów są normalizowane do lowercase.

#### 5.3.1. `GET /custom-pokemons` – lista własnych pokemonów

Zwraca listę (paginowaną) własnych Pokemonów.

**Query params:**

- `per_page` – opcjonalne, integer, domyślnie `15`.

**Odpowiedź `200 OK`:**  
Korzysta ze standardowej paginacji Laravel + `CustomPokemonResource`.

Przykładowa odpowiedź:

```json
{
  "data": [
    {
      "name": "moja-nazwa",
      "height": 10,
      "weight": 100,
      "types": ["fire"],
      "stats": {
        "hp": 100,
        "attack": 50
      },
      "description": "Customowy pokemon",
      "is_custom": true
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/custom-pokemons?page=1",
    "last": "http://localhost:8000/api/custom-pokemons?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "http://localhost:8000/api/custom-pokemons",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

#### 5.3.2. `POST /custom-pokemons` – dodanie własnego pokemona

**Body (JSON):**

```json
{
  "name": "moja-nazwa",
  "types": ["fire", "flying"],
  "height": 10,
  "weight": 100,
  "stats": {
    "hp": 100,
    "attack": 50
  },
  "description": "Opis mojego pokemona"
}
```

Reguły walidacji (skrótowo):

- `name` – **wymagane**, string,
- `types` – opcjonalne, tablica stringów,
- `height`, `weight` – opcjonalne, integer `>= 1`,
- `stats` – opcjonalne, tablica,
- `description` – opcjonalne, string.

Dodatkowa logika (w serwisie):

- nie można dodać Pokemona, który ma taką samą nazwę jak:
    - istniejący custom pokemon w bazie,
    - istniejący pokemon w PokeAPI.

**Odpowiedź `201 Created`:**

```json
{
  "name": "moja-nazwa",
  "height": 10,
  "weight": 100,
  "types": ["fire", "flying"],
  "stats": {
    "hp": 100,
    "attack": 50
  },
  "description": "Opis mojego pokemona",
  "is_custom": true
}
```

W przypadku konfliktu nazwy zwracany jest `422 Unprocessable Entity` z komunikatem z serwisu.

#### 5.3.3. `GET /custom-pokemons/{id}` – szczegóły własnego pokemona

**Parametry ścieżki:**

- `id` (w kodzie `customPokemon`) – integer, id w tabeli `custom_pokemons`.

**Odpowiedź `200 OK`:**

```json
{
  "name": "moja-nazwa",
  "height": 10,
  "weight": 100,
  "types": ["fire"],
  "stats": {
    "hp": 100,
    "attack": 50
  },
  "description": "Opis mojego pokemona",
  "is_custom": true
}
```

**Odpowiedź `404 Not Found`:** jeśli nie istnieje (obsługiwane przez route model binding).

#### 5.3.4. `PUT /custom-pokemons/{id}` – aktualizacja własnego pokemona

**Body (JSON):**

```json
{
  "types": ["water"],
  "height": 8,
  "weight": 90,
  "stats": {
    "hp": 90,
    "attack": 40
  },
  "description": "Zaktualizowany opis"
}
```

- Wszystkie pola są opcjonalne (brak pola = brak zmiany).

**Odpowiedź `200 OK`:**

```json
{
  "name": "moja-nazwa",
  "height": 8,
  "weight": 90,
  "types": ["water"],
  "stats": {
    "hp": 90,
    "attack": 40
  },
  "description": "Zaktualizowany opis",
  "is_custom": true
}
```

#### 5.3.5. `DELETE /custom-pokemons/{id}` – usunięcie własnego pokemona

Usuwa wskazanego custom pokemona.

**Odpowiedź `204 No Content`**

---

## 6. Cache PokeAPI

Cache danych z PokeAPI jest zaimplementowany w osobnym serwisie (np. `PokeApiCacheService`), który:

- korzysta z `PokeApiClient` do realnych wywołań HTTP,
- przechowuje odpowiedzi w wbudowanym mechanizmie cache Laravel (`Cache`),
- używa klucza w formie `pokeapi.pokemon.{name}`,
- TTL cache jest liczony **do najbliższego 12:00** (`Europe/Warsaw`).

Endpoint `/info` wykorzystuje ten serwis cache, dzięki czemu kolejne zapytania o te same Pokemony w ciągu dnia nie generują dodatkowych wywołań do PokeAPI.

---

## 7. OpenAPI / Swagger

Deklarację OpenAPI (YAML) dla tego API można umieścić np. w pliku `openapi.yaml` w katalogu głównym projektu i wykorzystać w Swagger UI / Redoc.
