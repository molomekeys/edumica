<?php

namespace Tests\Unit;

use App\Support\Nclc;
use PHPUnit\Framework\TestCase;

class NclcTest extends TestCase
{
    public function test_niveau_atteint_selon_le_bareme(): void
    {
        $this->assertSame('7', Nclc::niveauPour('co', 458));
        $this->assertSame('6', Nclc::niveauPour('co', 457));
        $this->assertSame('10+', Nclc::niveauPour('ce', 600));
        $this->assertSame('6', Nclc::niveauPour('ee', 9));
        $this->assertNull(Nclc::niveauPour('eo', 5));
    }

    public function test_plage(): void
    {
        $this->assertSame('6', Nclc::plage([6, 6]));
        $this->assertSame('458 – 502', Nclc::plage([458, 502]));
    }
}
