CryptoApp V5

This is a web based application for simulating cryptocurrency trade.

Application features:
- List top cryptocurrencies
- Search cryptocurrency by its ticking symbol
- Purchase cryptocurrency using virtual money
- Sell cryptocurrency
- Display current state of your wallet
- Display transaction list, what trades you have made

Application utilizes external resources:
https://coinmarketcap.com/ API
https://coinpaprika.com/ API as backup failsafe repository

Setup steps:
1. git clone https://github.com/ambivalent-axiom/crypto_trade.git
2. composer install
3. Update .env.example file with Your https://coinmarketcap.com/ API key.
4. php -S localhost:8000
5. Open localhost:8000 in web browser.