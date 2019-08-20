<?php

namespace Testgear;

trait HttpAssertable {

    /**
     * @var \PHPUnit\Framework\TestCase
     */
    protected $testCase;

    public function setTestCase(\PHPUnit\Framework\TestCase $testCase) {
        $this->testCase = $testCase;
    }

    public function assertSee(string $expectedString) {
        $message = 'Application response does not contain expected string.';
        $this->testCase->assertStringContainsString($expectedString, $this->result, $message);
        return $this;
    }

    public function assertDontSee(string $expectedString) {
        $message = 'Application response contains expected string.';
        $this->testCase->assertStringNotContainsString($expectedString, $this->result, $message);
        return $this;
    }

    public function assertJson(array $data) {
        $result = false;
        $actualData = json_decode($this->result, true);

        // Compare arrays
        $intersect = array_uintersect_assoc($actualData, $data, function($a, $b) {
            return $a <=> $b;
        });

        if ($intersect === $data) {
            $result = true;
        }

        $message = 'Application response does not contain expected JSON.';
        $this->testCase->assertTrue($result, $message);
        return $this;
    }

    public function assertExactJson($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }

        $actualData = json_encode(json_decode($this->result, true));
        $message = 'Expected JSON does not match application response.';
        $this->testCase->assertJsonStringEqualsJsonString($data, $actualData, $message);
        return $this;
    }

    public function assertStatus($status) {
        if ($this->status === null) {
            $this->status = 200;
        }

        $message = 'Application status code does not equal to expected.';
        $this->testCase->assertTrue($this->status == $status, $message);
        return $this;
    }

    public function assertStatusNot($status) {
        if ($this->status === null) {
            $this->status = 200;
        }

        $message = 'Application status code equals to expected.';
        $this->testCase->assertNotTrue($this->status == $status, $message);
        return $this;
    }

    public function assertHeader($headerName, $value = null) {
        $result = false;

        if (isset($this->header[$headerName])) {
            $result = true;

            if ($value) {
                $result = $this->header[$headerName] == $value;
            }
        }

        $this->testCase->assertTrue($result);
        return $this;
    }

    public function assertHeaderMissing(string $headerName, string $value = null) {
        $result = false;

        if (isset($this->header[$headerName])) {
            $result = true;

            if ($value) {
                $result = $this->header[$headerName] == $value;
            }
        }

        $this->testCase->assertNotTrue($result);
        return $this;
    }

    public function assertCookie(string $name, string $value = null) {
        $result = false;
        $expectedFormat = $value ? "/^$name=$value;/" : "/^$name=\w+;/";

        foreach ($this->cookie as $cookie) {
            $result = preg_match($expectedFormat, $cookie) ? true : false;
        }

        $this->testCase->assertTrue($result, 'Expected cookie not found in application response.');
        return $this;
    }

    public function assertCookieMissing(string $name, string $value = null) {
        $result = false;
        $expectedFormat = $value ? "/^$name=$value;/" : "/^$name=\w+;/";

        foreach ($this->cookie as $cookie) {
            $result = preg_match($expectedFormat, $cookie) ? true : false;
        }

        $this->testCase->assertNotTrue($result, 'Expected cookie found in application response.');
        return $this;
    }

}