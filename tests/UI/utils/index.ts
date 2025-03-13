import {
    // Import BO
    boProductsCreatePage
  } from '@prestashop-core/ui-testing';
//because we have an exception with page title, it's currently "Products" instead of "Product"
const getExpectedProductTitle = ():string=> {
    let expectedTitle = boProductsCreatePage.pageTitle;
    if(expectedTitle.endsWith("s")){expectedTitle=expectedTitle.substring(0,expectedTitle.length-1)};
    return expectedTitle;
} 
export default getExpectedProductTitle;