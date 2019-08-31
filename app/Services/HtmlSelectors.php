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
    static $LoginPageTitle   = 'Login';
    static $HomePageTitle    = 'STS Prepayment Vending Stystem';
    static $VendingPageTitle = 'Electric Meter Vending';

    // Input Fields
    static $UsernameField         = "//input[@id='username']";
    static $PasswordField         = "//input[@id='password']";
    static $MeterCodeField        = "//input[@name='txtMeterCode']";
    static $MeterIdField          = "//input[@name='txtIDCode']";
    static $AddressField          = "//input[@name='txtAddr']";
    static $ContractIdField       = "//input[@name='txtContractID']";
    static $TariffField           = "//input[@name='txtPrice']"; // Price Field
    static $TariffTypeField       = "//input[@name='txtUseType']"; // Use Type Field
    static $MeterTypeField        = "//input[@name='txtMeterType']";
    static $AreaField             = "//input[@name='txtArea']";
    static $TokenField            = "//input[@name='txtToken']";
    static $EnergyField           = "//input[@name='txtEnergy']";
    static $AmountField           = "//input[@name='txtAmount']";
    static $RegistrationDateField = "//input[@name='txtRegDT']";
    static $LastVendingDateField  = "//input[@name='txtLastDT']";
    static $VATField              = "//input[@name='txtVAT']";

    // Buttons
    static $LoginButton           = "//input[@type='submit']";
    static $MeterVendingButton    = "//div[text()='Electric Meter Vending']";
    static $MeterCodeSearchButton = "//input[@name='txtMeterCode']/preceding-sibling::input/preceding-sibling::span/a";

    // Scripts
    static $MeterVendingPopupScript = "ShowElectricVending();";
    static $SearchMeterCodeScript   = "DoSearchByMeterCode();";

    // URLs
    static $VendingPageUrl = "/Vending/Electric";

    // text
    static $NoRecordFoundText = "no record";

}