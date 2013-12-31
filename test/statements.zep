namespace Test;

class Statements
{
    /**
     * Vars for 544 Issue test
     */
    private totalSteps = 100;

    private width = 100;

    private filledChar = "=";

    private unfilledChar = ".";

    private arrow = ">";

    public function testElseIf(int num)
    {
        if num > 0 {
            return "more";
        } elseif num == 0 {
            return "equal";
        } elseif num == -1 {
            return "-1";
        } else {
            return "less";
        }
    }

    public function testElseIf1(int num)
    {
        var total = 10;

        if (num < total) {
            return "less";
        } elseif (num == total) {
            return "equal";
        } else {
            return "else";
        }
    }

    public function testElseIf2(var num, var total)
    {
        if (num < total) {
            return "less";
        } elseif (num == total) {
            return "equal";
        } else {
            return "else";
        }
    }

	public function test544Issue(int! step) {
		int filledWidth, unfilledWidth;

		if step < this->totalSteps {
			let filledWidth = (this->width - 1) / this->totalSteps * step;
			let unfilledWidth = (this->width - 1) - filledWidth;
			return str_repeat(this->filledChar, filledWidth).this->arrow.str_repeat(this->unfilledChar, unfilledWidth);
		} elseif step === this->totalSteps {
			return str_repeat(this->filledChar, this->width)."\n";
		} else {
			return str_repeat(this->unfilledChar, this->width);
		}
	}

	public function test544IssueWithVariable(int! step) {
		int filledWidth, unfilledWidth, totalSteps;

        let totalSteps = this->totalSteps;

		if step < totalSteps {
			let filledWidth = (this->width - 1) / totalSteps * step;
			let unfilledWidth = (this->width - 1) - filledWidth;
			return str_repeat(this->filledChar, filledWidth).this->arrow.str_repeat(this->unfilledChar, unfilledWidth);
		} elseif step === totalSteps {
			return str_repeat(this->filledChar, this->width)."\n";
		} else {
			return str_repeat(this->unfilledChar, this->width);
		}
	}
}
