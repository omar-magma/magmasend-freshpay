# Introduction

This README is a guide for creating a BOILERPLATE SDK intended for MagmaSend partners. The SDK is designed to simplify
the integration of MagmaSend's payment services into various applications.

- **SDK Folder Naming Convention**: The SDK folder should be named according to the following
  convention: `magmasend-payout-sdk-<name of the sdk>`.
- **Modification of the composer.json File**: After creating the SDK, you need to modify the `composer.json` file to
  replace "Boilerplate" with the name of your SDK.

```composer.json
"autoload": {
  "psr-4": {
    "App\\SDK\\VotreNomSDK\\": ""
  }
}
```

# Installation

To install the SDK, you need to run the following command:

```bash
composer install
```

# Utilisation

The SDK is designed to be used in PHP applications.

## RequestClass

The RequestClass is the main class of the SDK. It contains methods for making calls to MagmaSend's payment services.

You have 3 methods that you need to implement in your SDK:

- getBalance($transaction = null): array - Retrieves the balance of the payment account.
- doTransaction($transaction): array - Executes a funds transfer.
- checkTransaction($transaction): array - Checks the status of a transaction.
- checkAccount($transaction): ?array - Not required if the partner does not have an endpoint for account verification.

## DefaultClass

The `DefaultClass` is designed to facilitate communication with the MagmaSend API. It is structured to perform HTTP
requests while managing specific configurations for these requests. You can customize it according to your needs.

# Tests

You can test your SDK by running the unit tests.
To do so, you need to execute the following command:

```bash
./vendor/bin/phpunit
```

# Logs

In the `logs` folder, you will find the logs for your SDK. You can use these logs to debug your SDK.
