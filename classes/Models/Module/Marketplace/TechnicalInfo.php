<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Models\Module\Marketplace;

class TechnicalInfo
{
    /** @var string */
    public $publicationDate = '';
    /** @var Release[] */
    public $releases = [];
    /** @var ?Release */
    public $lastRelease = null;

    /**
     * @param array{
     *     publication_date?: string,
     *     releases?: array<int, mixed>,
     *     last_release?: array<int, mixed>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $obj = new self();

        $obj->publicationDate = $data['publication_date'] ?? '';

        $obj->releases = [];
        if (!empty($data['releases'])) {
            foreach ($data['releases'] as $releaseData) {
                $obj->releases[] = Release::fromArray($releaseData);
            }
        }

        $obj->lastRelease = !empty($data['last_release'])
            ? Release::fromArray($data['last_release'])
            : null;

        return $obj;
    }
}
