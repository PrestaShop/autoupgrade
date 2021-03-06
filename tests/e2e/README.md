# Autoupgrade tests
Tests for 1 click upgrade module.

Test is compatible with different PS versions
- `1.7.4` to `1.7.5 - 1.7.6 - 1.7.7 - 1.7.8`
- `1.7.5` to `1.7.6 - 1.7.7 - 1.7.8`
- `1.7.6` to  `1.7.7 - 1.7.8`
- `1.7.7` to `1.7.8`

We use [Mocha](https://mochajs.org/), [Playwright](https://github.com/microsoft/playwright) and 
[Chai](https://www.chaijs.com/) as our base stack.

## How to install your environment

```bash
# Download the new PS version that you want to upgrade
https://www.prestashop.com/fr/versions-precedentes
cd autoupgrade_tests/
npm install
```

### Test parameters
| Parameter             | Description      |
|-----------------------|----------------- |
| URL_FO                | URL of your PrestaShop website Front Office (default to **`http://localhost/prestashop/`**) |
| LOGIN                 | LOGIN of your PrestaShop website (default to **`demo@prestashop.com`**) |
| PASSWD                | PASSWD of your PrestaShop website (default to **`prestashop_demo`**) |
| PS_VERSION            | Your prestashop version (expl **`1.7.5`**) |
| PS_VERSION_UPGRADE_TO | The new version to upgrade (example **`1.7.7`**) |
| PROJECT_PATH          | The prestashop project path (example **`/projet/prestashop_1.7.4.4`**) |
| DOWNLOAD_PATH         | Your download path where there is the zip of the new version to upgrade (default to **`/home/username/Téléchargements`**) |
| ZIP_NAME              | The new version to upgrade zip name (example **`prestashop_1.7.7.0.zip`**) |

Before running upgrade test, you should install an old version of PS.

### Launch the script

`URL_FO=your_shop_url PROJECT_PATH=your_project_path PS_VERSION=your_ps_version PS_VERSION_UPGRADE_TO=new_ps_version DOWNLOAD_PATH=your_download_path ZIP_NAME=zip_name.zip npm run test`

Enjoy :wink: :v: