<?php
/**
 * Created by PhpStorm.
 * User: devert
 * Date: 8/29/19
 * Time: 10:22 AM
 */

namespace App\Services;


class HtmlSelectors
{
    // Page Titles
    static $LoginPageTitle = 'Login';
    static $HomePageTitle = 'STS Prepayment Vending Stystem';
    static $VendingPageTitle = 'Electric Meter Vending';

    // Input Fields
    static $UsernameField = "//input[@id='username']";
    static $PasswordField = "//input[@id='password']";
    static $MeterCodeField = "//input[@placeholder='Please Input Meter Code']";

    // Buttons
    static $LoginButton = "//input[@type='submit']";
    static $MeterVendingButton = "//div[text()='Electric Meter Vending']";
    static $MeterCodeSearchButton = "//input[@name='txtMeterCode']/preceding-sibling::input/preceding-sibling::span/a";
//    static $MeterCodeSearchButton = "(//a[contains(@class,'searchbox-button')])[2]";

    // Scripts
    static $MeterVendingPopupScript = "ShowElectricVending();";
    static $SearchMeterCodeScript = "DoSearchByMeterCode();";

    // URLs
    static $VendingPageUrl = "/Vending/Electric";
}