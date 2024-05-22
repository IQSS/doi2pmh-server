<?php

namespace App\Services\Oai\Arguments;

use DOMElement;
use Exception;
use App\Services\Oai\Verbs\OaiVerbInterface;

class ResumptionToken implements ArgumentInterface
{
    use ArgumentTrait;

    /**
     * Max results
     */
    const ELEMENTS_PER_PAGE = 15;

    /**
     * Validity in seconds of the resumption token.
     */
    const SECONDS_BEFORE_EXPIRATION = 60 * 24 * 24;

    /**
     * Format of the expiration date.
     */
    const DATE_FORMAT = "Y-m-d\TH:i:s+01:00";

    /**
     * @var int Start results at
     */
    private int $cursor = 0;

    private string $expirationDate;

    private int $listSize;

    public function __construct(OaiVerbInterface $verb)
    {
        $this->verb = $verb;
        $this->name = 'resumptionToken';
        $this->setValue($verb->getRequest()->get('resumptionToken')?? $verb->getRequest()->get('amp;resumptionToken'));
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * @return bool Return true if the resumptionToken is needed
     */
    public function shouldBeIncluded(): bool
    {
        return $this->getListSize() > ResumptionToken::ELEMENTS_PER_PAGE
                && $this->getCursor() < $this->getListSize();
    }

    /**
     * @return int      Return the resumption token cursor to paginate results
     */
    public function getCursor(): int
    {
        return $this->cursor;
    }

    /**
     * @return int      Return the total number of records
     */
    public function getListSize(): int
    {
        return $this->listSize;
    }

    /**
     * @param int $listSize
     */
    public function setListSize(int $listSize): void
    {
        $this->listSize = $listSize;
    }

    /**
     * Increment the cursor if results are paginated
     * @return $this
     */
    public function incrementCursor(): self
    {
        if ($this->listSize > $this->cursor) {
            $this->cursor +=  ((int) self::ELEMENTS_PER_PAGE) ? self::ELEMENTS_PER_PAGE : 0;
        }
        return $this;
    }

    /**
     * Returns the expiration date associated with this resumption token.
     */
    public function getExpirationDate(): string
    {
        if (!isset($this->expirationDate))
        {
            $this->setExpirationDate();
        }
        return $this->expirationDate;
    }

    /**
     *  Set the resumption token expiration date (now + 1 day)
     */
    private function setExpirationDate()
    {
        $this->expirationDate = date(self::DATE_FORMAT, strtotime("now") + self::SECONDS_BEFORE_EXPIRATION);
    }

    /**
     * @return DOMElement       Increment cursor, set expiration date and return the resumption token
     */
    public function generateToken(): DOMElement
    {
        $content = 'expirationDate=' . $this->getExpirationDate() . ',';

        foreach ($this->verb->getArguments() as $argument) {
            if ($argument->getValue() && $argument->isInResumptionToken() ) {
                $content .= $argument->getName() . '=' . $argument->getValue()  . ',';
            }
        }
        $content .= 'cursor=' . $this->cursor;

        return $this->verb->getOaiService()->createElement('resumptionToken', $content, [
            'expirationDate' => $this->expirationDate,
            'completeListSize'=> $this->getListSize(),
            'cursor' => $this->cursor
        ]);
    }

    /**
     * Parse the resumption token and the set the arguments value.
     * The resumption token format is argumentName=argumentValue,argumentName=argumentValue...
     * @throws Exception
     */
    public function parseTokenContent()
    {
        // explode arguments
        $arguments = explode(',', $this->value);

        // explode the first argument which is expiration date
        $expirationDate = explode('=', $arguments[0]);

        // Verify that the expiration date exist and is not expired
        if (
            empty($expirationDate) ||
            $expirationDate[0] !== 'expirationDate' ||
            !isset($expirationDate[0]) ||
            date(strtotime($expirationDate[1])) < date(strtotime('now'))
        ) {
            throw new Exception("badResumptionToken");
        } else {
            $this->expirationDate = $expirationDate[1];
        }

        // then delete expiration date from exploded arguments
        unset($arguments[0]);

        $verbArguments = $this->verb->getArguments();

        foreach ($arguments as $argument) {

            $arg = explode('=', $argument);

            if (isset($arg[0]) && isset($arg[1]) ) {

                // Cursor is not an official argument
                if ($arg[0] == 'cursor') {
                    $this->cursor = $arg[1];
                } else {
                    $verbArguments[$arg[0]]->setValue($arg[1]);
                }
            }

        }
    }

    /**
     * @see ArgumentInterface
     * @return bool
     */
    public function isInResumptionToken(): bool
    {
        return false;
    }
}
