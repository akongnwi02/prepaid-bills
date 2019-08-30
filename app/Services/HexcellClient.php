<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/27/19
 * Time: 9:34 PM
 */

namespace App\Services;

use App\Exceptions\ResourceNotFoundException;

class HexcellClient extends WebDriverHelper
{
    protected $meter;

    /**
     * HexcellClient constructor.
     * @param Meter $meter
     */
    public function __construct(Meter $meter)
    {
        $this->meter = $meter;
        return parent::__construct();
    }

    /**
     * @param $meterCode
     * @return Meter
     * @throws ResourceNotFoundException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function searchMeter($meterCode) : Meter
    {
        $this->openPage(config('app.hexcell_credentials.url'));
        $this->waitForPage(HtmlSelectors::$LoginPageTitle);
        $this->fillField(HtmlSelectors::$UsernameField, config('app.hexcell_credentials.username'));
        $this->fillField(HtmlSelectors::$PasswordField, config('app.hexcell_credentials.password'));
        $this->click(HtmlSelectors::$LoginButton);
        $this->waitForPage(HtmlSelectors::$HomePageTitle);

        $this->openPage(config('app.hexcell_credentials.url') . HtmlSelectors::$VendingPageUrl);
        $this->waitForPage(HtmlSelectors::$VendingPageTitle);

        $this->executeJs(sprintf("$('#txtMeterCode').searchbox('setValue', %s);", $meterCode));

        $this->webDriver->wait(2);

        $this->executeJs(HtmlSelectors::$SearchMeterCodeScript);

        $this->webDriver->wait(30);
        $this->webDriver->takeScreenshot('screenshots.png');
        $this->webDriver->quit();

        if ($this->meter) {
            return $this->meter;
        }
        throw new ResourceNotFoundException(Meter::class, $meterCode);

    }

    public function generateToken()
    {

    }

}