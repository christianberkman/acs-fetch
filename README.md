# acs-fetch
Fetch appointment dates from the [American Ciizen Services (ACS) Appointment System](https://evisaforms.state.gov/Instructions/ACSSchedulingSystem.asp)

## Example Usage
```php
<?php
    require __DIR__ . '/acs-fetch.php';
    $acs = new ACSFectch();
    $acs->countryCode = "UGA";
    $acs->postCode = "KMP";
    $acs->navigate();
    $acs->fetchCalendar();
    print_r($acs->dates);
```

For a more eomperhensive example, see [example.php](example.php)

## Sample output
```php
Array
(
    [0] => Array
        (
            [date] => 2023-08-21
            [status] => booked
        )

    [1] => Array
        (
            [date] => 2023-08-24
            [status] => booked
        )

    [2] => Array
        (
            [date] => 2023-08-28
            [status] => booked
        )
    // continued...
)
```

## Public Methods

### Constructor
Class consructor, initiates cURL handler and cookiejar
```php
public function __construct(?string $customCookieJar = null)
```
* `` string $customCookieJar`` custom path to the cookie jar

### navigate
Navigate through URLs to reach calendar
```php
public function navigate() : void
```

### fetchCalendar
Fetch calendar page and extract dates, append to date array
```php
public function fetchCalendar(int $month = 0, int $year = 0) : void
```
* ``int $month`` Month to fetch (no leading)
* ``int $year`` Year to fetch

## Public properties

### countryCode
``string`` "Consulate/Embassy Country" field, e.g. ``UGA`` for Uganda.

### postCode
``string`` "Consulate/Embassy City" field, e.g. ``KMP`` for Kampala.

### cookie
``string`` Path to cookiejar

### ignoreUnavailable
``boolean`` Ignore unavailable dates, default ``true``