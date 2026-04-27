/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
export function maskSensitiveInfoInUrl(url: string, adminFolder: string): string {
  const placeHolder = '********';
  const adminFolderRegex = new RegExp(adminFolder, 'g');
  const maskedUrl = url.replace(adminFolderRegex, placeHolder);

  const tokenRegex = new RegExp('&token=[^&]*', 'gi');
  return maskedUrl.replace(tokenRegex, `&token=${placeHolder}`);
}
