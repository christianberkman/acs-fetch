<?php
/**
 * acs-fech
 * Fetch appointment dates from the American Ciizen Services (ACS) Appointment System
 *
 * Class file
 *
 * 2023 by Christian Berkman
 * https://github.com/christianberkman/acs-fetch
 */

class ACSFetch{
    /**
     * Options
     */
    public  $countryCode,                       // Country
            $postCode,                          // Consulate/Embassy
            $cookie = __DIR__ . '/cookie.txt',  // Path to cookie jar
            $ignoreUnavailble = true;           // Ignore unavailable dates

    /**
     * Internal variables
     */
    private $ch,                            // cURL Handler
            $token;                         // CSRF Token
    
    /**
     * Output
     */
    public $dates = [];                     // Array with fetched dates

    /**
     * Constructor
     */
    public function __construct(?string $customCookieJar = null){
        // Cookie jar
        if(!empty($customCookieJar)) $this->cookie = $customCookieJar;
        if(!is_writable($this->cookie)) throw new \Exception("Cookiejar is not writable: {$this->cookie}");
    
        // Init CURL Handler
        $this->ch = curl_init();
        curl_setopt_array($this->ch, 
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_COOKIEFILE => $this->cookie,
            CURLOPT_COOKIEJAR => $this->cookie
        ]);
    }

    /**
     * Navigate through URLs to reach calendar
     */
    public function navigate() : void{
       // Page 1: Select location, extract CSRF Token
       $url1 = 'https://evisaforms.state.gov/Instructions/ACSSchedulingSystem.asp';
       $page1 = $this->get($url1);
       $this->token = $this->extractToken($page1);

       // Page 2: Make Appointment Button
       $url2 = "https://evisaforms.state.gov/acs/default.asp?CSRFToken={$this->token}&PostCode={$this->postCode}&CountryCode={$this->countryCode}&CountryCodeShow=&PostCodeShow=&Submit=Submit";
       $page2 = $this->get($url2);

       // Page 3: Select Service
       $url3 = "https://evisaforms.state.gov/acs/make_default.asp?pc={$this->postCode}&CSRFToken={$this->token}";
       $page3 = $this->get($url3);
    }

    /**
     * Fetch calendar page and extract dates, append to date array
     */
    public function fetchCalendar(int $month = 0, int $year = 0) : void{
        // Month and year
        if(empty($month)) $month = date('n');
        if(empty($year)) $year = date('Y');
        
        // Fetch calendar page
        $url = "https://evisaforms.state.gov/acs/make_calendar.asp?type=1&servicetype=AA&pc={$this->postCode}&CSRFToken={$this->token}&nMonth={$month}&nYear={$year}";
        $html = $this->get($url);

        // DOM Document
        $dom = new domDocument;
        @$dom->loadHTML($html);
        
            // Table
            $table = $dom->getElementByID('Table3');
            
            // Rows
            $rows = $table->getElementsByTagName('tr');
            foreach($rows as $row){

                // Columns
                $cols = $row->getElementsByTagName('td');
                foreach($cols as $col){
                    $color = $col->getAttribute('bgcolor');
                    $status = $this->getStatus($color);
                    $text = $col->textContent;
                    $day = intval($text);
                    if($day == 0) continue; // Column is not an actual date
                    if($status == 'unavailable' && $this->ignoreUnavailble) continue; // Ignore unavailable dates
                    
                    // Append datee to array
                    $this->dates[] = [
                        'date' => "{$year}-". str_pad($month, 2, 0, STR_PAD_LEFT) ."-". str_pad($day, 2, 0, STR_PAD_LEFT),
                        'status' => $this->getStatus($color)
                    ];
                }
            }
    }

    /**
     * Send GET request using cURL
     */
    private function get(string $url) : string{
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $response = curl_exec($this->ch);
        if($response === false){
            throw new \Exception("cURL error: " .curl_error($this->ch) );
        }

        return $response;
    }

    /**
     * Extract CSRF Token from HTML
     */
    private function extractToken(string $html){
        $pattern = '/CSRFToken\" value=\"([A-Z0-9]+)\"/m';
        preg_matcH_all($pattern, $html, $matches);
        if(count($matches) == 0){
            throw new \Exception('Could not extract token');
        }
        return $matches[1][0];
    }

    /**
     * Get date status from color
     */
    private function getStatus(string $color){
        $color = strtolower($color);
        switch($color){
            case '#c0c0c0': return 'unavailable';   break;
            case '#add9f4': return 'booked';        break;
            case '#ffffc0': return 'available';     break;
            default:        return 'unavailable';        break;
        }
    }

 }