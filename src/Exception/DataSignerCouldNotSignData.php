<?php

declare(strict_types=1);

namespace PetrKnap\DataSigner\Exception;

use PetrKnap\DataSigner\SignableDataInterface;
use PetrKnap\Shorts\Exception\CouldNotProcessData;

/**
 * @extends CouldNotProcessData<SignableDataInterface|string>
 */
final class DataSignerCouldNotSignData extends CouldNotProcessData implements DataSignerException
{
}
