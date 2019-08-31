<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/30/19
 * Time: 9:22 AM
 */

namespace App\Services;


use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;

class WebDriverHelper
{
    /**
     * @var RemoteWebDriver
     */
    protected $webDriver;

    /**
     * WebDriverHelper constructor.
     */
    public function __construct()
    {
        $this->webDriver = $this->getWebDriver();
    }

    /**
     * @return RemoteWebDriver
     */
    public function getWebDriver()
    {
        $opts = new ChromeOptions();
        $caps = DesiredCapabilities::chrome();
        $opts->addArguments([ "--headless","--disable-gpu", "--no-sandbox" ]);
        $caps->setCapability(ChromeOptions::CAPABILITY, $opts);
        $driver = RemoteWebDriver::create(config('app.hub_url'), $caps);
        return $driver;
    }

    /**
     * @param $field
     * @param $value
     */
    public function fillField($field, $value)
    {
        $fieldElement = $this->webDriver->findElement(WebDriverBy::xpath($field));
        $fieldElement->clear();
        $fieldElement->sendKeys($value);
    }

    /**
     * @param $button
     */
    public function click($button)
    {
        $buttonElement = $this->webDriver->findElement(WebDriverBy::xpath($button));
        $buttonElement->click();
    }

    public function clickSearchButton($button)
    {
        $buttonElement = $this->webDriver->findElement(WebDriverBy::xpath($button));
        $this->webDriver->wait(1);
        $this->executeJs("arguments[0].click()", [$buttonElement]);

    }

    /**
     * @param $url
     */
    public function openPage($url)
    {
        $this->webDriver->get($url);
    }

    /**
     * @param $pageTitle
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitForPage($pageTitle)
    {
        $this->webDriver->wait()->until(WebDriverExpectedCondition::titleIs($pageTitle));
    }

    /**
     * @param $query
     * @param array $arguments
     */
    public function executeJs($query, $arguments = [])
    {
        $this->webDriver->wait(1);
        $this->webDriver->executeScript($query, $arguments);
    }

    /**
     * @param $field
     */
    public function pressEnter($field)
    {
        $fieldElement = $this->webDriver->findElement(WebDriverBy::xpath($field));
        $fieldElement->sendKeys(WebDriverKeys::RETURN_KEY);
    }

    /**
     * @param $field
     */
    public function submit($field)
    {
        $fieldElement = $this->webDriver->findElement(WebDriverBy::xpath($field));
        $fieldElement->submit();
    }

    /**
     * @param $meterCode
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     * @throws \Facebook\WebDriver\Exception\TimeOutException
     */
    public function waitForResult($meterCode)
    {
        $this->webDriver->wait()
            ->until(WebDriverExpectedCondition
                ::elementValueContains(WebDriverBy
                    ::xpath(HtmlSelectors::$MeterIdField), $meterCode));
    }

    /**
     * @param $text
     * @return bool
     */
    public function pageContainsText($text) : bool
    {
        return strpos($this->webDriver->getPageSource(), $text) !== false;
    }

    /**
     * @param $field
     * @return string
     */
    public function getValue($field) : string
    {
        $fieldElement = $this->webDriver->findElement(WebDriverBy::xpath($field));
        return $fieldElement->getAttribute('value');
    }


}