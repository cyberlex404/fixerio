# Fixer.io
The module provides a service for currency conversion

## Installation
Get your API key fixer.io and fill it in the settings form or in settings.php 
`$config['fixerio.settings']['api_access_key'] = 'YOUR_API_KEY';`

## Usage example
```$value = \Drupal::service("fixerio.exchange")->convert(2.90, "USD", "EUR");```
## Road map

- Cache tags
- 
