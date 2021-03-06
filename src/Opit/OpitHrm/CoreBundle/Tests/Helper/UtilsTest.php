<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\Tests\Helper;

use Opit\Component\Utils\Utils;
use Symfony\Component\Form\Test\TypeTestCase;
use Opit\OpitHrm\CoreBundle\Tests\Form\TestType;

/**
 * Description of UtilsTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CoreBundle
 */
class UtilsTest extends TypeTestCase
{
    /**
     * testing getClassBasename method.
     */
    public function testGetClassBasename()
    {
        $utils = new Utils();
        $result = Utils::getClassBasename($utils);

        $this->assertEquals('Utils', $result, 'Getting class\'s name.');
    }

    /**
     * testing validateDate method.
     */
    public function testValidateDate()
    {
        $this->assertTrue(Utils::validateDate(date('2014-01-01'), 'Y-m-d'), 'ValidateDate: invalid date.');
        $this->assertTrue(Utils::validateDate(date('2014-01-01 15:34:56')), 'ValidateDate: invalid datetime.');
        $this->assertFalse(Utils::validateDate(date('2014-02-30'), 'Y-m-d'), 'ValidateDate: valid date.');
        $this->assertFalse(Utils::validateDate(date('2014-02-30 25:02:78')), 'ValidateDate valid datetime.');
        $this->assertTrue(
            Utils::validateDate(date('2014/03/10 12:35:10'), 'Y/m/d H:i:s'),
            'ValidateDate: The given datetime and its format are not covenant.'
        );
        $this->assertFalse(
            Utils::validateDate(date('2014/03/10'), 'Y-m-d H:i:s'),
            'ValidateDate: The given date and its format are covenant.'
        );
    }

    /**
     * testing validateCurrencyCodeString method.
     */
    public function testValidateCurrencyCodesString()
    {
        $this->assertTrue(
            Utils::validateCurrencyCodesString('HUF'),
            'ValidateCurrencyCodesString: HUF is invalid currency code.'
        );
        $this->assertTrue(
            Utils::validateCurrencyCodesString('HUF,EUR'),
            'ValidateCurrencyCodesString: HUF,EUR are invalid currency codes.'
        );
        $this->assertFalse(
            Utils::validateCurrencyCodesString('HUFMuf'),
            'ValidateCurrencyCodesString: HUFMuf is valid currency code.'
        );
        $this->assertFalse(
            Utils::validateCurrencyCodesString('HU2'),
            'ValidateCurrencyCodesString: HU2 is valid currency code.'
        );
        $this->assertFalse(
            Utils::validateCurrencyCodesString('HUF|EUR'),
            'ValidateCurrencyCodesString: "|" is valid currency code seperator character.'
        );
    }

    /**
     * Test Utils::testArrayValueRecursive
     */
    public function testArrayValueRecursive()
    {
        $input = array(
            'a' => array(
                'findme' => 'Test a',
                'other' => 'Test',
                'sub' => array(
                    'findme' => 'Test b'
                )
            )
        );

        $result = Utils::arrayValueRecursive('findme', $input);

        $this->assertCount(2, $result, 'Utils::arrayValueRecursive: Count does not match.');
        $this->assertEquals(array('Test a', 'Test b'), $result, 'Utils::arrayValueRecursive: Arrays do not match.');
    }

    /**
     * Test Utils::groupingArrayByCounter
     */
    public function testgroupingArrayByCounter()
    {
        $input = array(
            array('User 1'), array('User 2'), array('User 3'), array('User 4'), array('User 5'), array('User 6')
        );

        $result = Utils::groupingArrayByCounter($input, 2);

        $this->assertCount(3, $result, 'Utils::groupingArrayByCounter: Count does not match.');
        $this->assertEquals(
            array(
                array(array('User 1'), array('User 2')),
                array(array('User 3'), array('User 4')),
                array(array('User 5'), array('User 6'))
            ),
            $result,
            'Utils::arrayValueRecursive: Arrays do not match.'
        );
    }

    public function testGetErrorMessages()
    {
        $type = new TestType();
        $form = $this->factory->create($type);
        // Add form error using template
        $form->addError(new \Symfony\Component\Form\FormError('Text field cannot be empty'))
            ->get('title')
            ->addError(
                new \Symfony\Component\Form\FormError(
                    '',
                    'Text field requires at least %count% characters',
                    array('%count%' => 4)
                )
            );

        $result = Utils::getErrorMessages($form);

        $this->assertCount(2, $result, 'Utils::getErrorMessages: Count does not match.');
        $this->assertEquals(
            'Text field cannot be empty',
            $result[0],
            'Utils::getErrorMessages: Error message not equal.'
        );
    }

    public function testDiffDays()
    {
        $result = Utils::diffDays(new \DateTime('2014-01-01'), new \DateTime('2014-01-15'));

        $this->assertCount(15, $result, 'Utils::diffDays: Count does not match.');
        $this->assertTrue($result[0] instanceof \DateTime, 'Utils::diffDays: Wrong object type.');

        // Test overlapping month including time
        $result2 = Utils::diffDays(new \DateTime('2014-01-30 08:00:00'), new \DateTime('2014-02-02 07:00:00'));

        $this->assertCount(3, $result2, 'Utils::diffDays: Count does not match.');
    }

    public function testBuildDoctrineQuery()
    {
        $eb = $this->getMockBuilder('\Doctrine\DBAL\Query\Expression\ExpressionBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $eb->expects($this->any())
            ->method('andX')
            ->will($this->returnSelf());
        $eb->expects($this->any())
            ->method('like')
            ->will($this->returnSelf());

        $qb = $this->getMockBuilder('\Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->any())
            ->method('leftJoin')
            ->will($this->returnSelf());
        $qb->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($eb));
        $qb->expects($this->once())
            ->method('getRootAliases')
            ->will($this->returnValue(array('u')));

        $input = array(
            'id' => '1',
            'username' => 'test',
            'employee' => array(
                'name' => 'Test Employee'
            )
        );
        $params = $andx = array();
        Utils::buildDoctrineQuery($qb, $input, $params, $andx);

        $this->assertCount(3, $params, 'Utils::buildDoctrineQuery: Count does not match.');
        $this->assertCount(count($andx), $params, 'Utils::buildDoctrineQuery: Andx count does not match params.');
        $this->assertJsonStringEqualsJsonString(
            '{"id":"%1%","username":"%test%","name":"%Test Employee%"}',
            json_encode($params),
            'Utils::buildDoctrineQuery: Json result does not match.'
        );
    }

    public function testarrayValuesToUpper()
    {
        $arr = array('role_user', 'role_admin');
        $result = Utils::arrayValuesToUpper($arr);

        $this->assertEquals('ROLE_USER', $result[0], 'Utils::arrayValuesToUpper Array input, result is not uppercase.');

        // Test string to array casting
        $value = 'role_admin';
        $result2 = Utils::arrayValuesToUpper($value);

        $this->assertEquals('ROLE_ADMIN', $result2[0], 'Utils::arrayValuesToUpper String input, result is not uppercase.');
    }

    public function testSanitizeString()
    {
        $input = "Opit-HRM - December 2014";
        $result = Utils::sanitizeString($input);

        $this->assertEquals('opit-hrm-december2014', $result, 'Utils::sanitizeString Content does not match.');

        $input2 = "Opit-HRM  Sven Henneböle - December ?! 2014";
        $result2 = Utils::sanitizeString($input2);

        $this->assertEquals('opit-hrmsvenhenneble-december2014', $result2, 'Utils::sanitizeString Content including special chars does not match.');
    }

    public function testGetHigherLevelRoles()
    {
        $hierarchy = array(
            'ROLE_CUSTOMER' => array(),
            'ROLE_USER' => array('ROLE_CUSTOMER'),
            'ROLE_PAYROLL' => array('ROLE_CUSTOMER'),
            'ROLE_STAKEHOLDER' => array('ROLE_CUSTOMER'),
            'ROLE_FINANCE' => array('ROLE_CUSTOMER'),
            'ROLE_TEAM_MANAGER' => array('ROLE_USER'),
            'ROLE_GENERAL_MANAGER' => array('ROLE_TEAM_MANAGER'),
            'ROLE_SYSTEM_ADMIN' => array('ROLE_CUSTOMER', 'ROLE_PAYROLL', 'ROLE_STAKEHOLDER', 'ROLE_FINANCE', 'ROLE_USER'),
            'ROLE_ADMIN' => array('ROLE_GENERAL_MANAGER', 'ROLE_SYSTEM_ADMIN'),
            'ROLE_SUPER_ADMIN' => array('ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'),
        );
        $expectedResult = array(
            'ROLE_USER',
            'ROLE_TEAM_MANAGER',
            'ROLE_GENERAL_MANAGER',
            'ROLE_SYSTEM_ADMIN',
            'ROLE_ADMIN',
            'ROLE_SUPER_ADMIN')
        ;
        $role = 'ROLE_USER';

        $result = Utils::getHigherLevelRoles($hierarchy, $role);

        $this->assertTrue(is_array($result), 'Utils::getHigherLevelRoles The result is not an array.');
        $this->assertEquals($expectedResult, $result, 'Utils::getHigherLevelRoles The expected result is not equal to received result.');
    }

    public function testGetformattedAmount()
    {
        $decimals = 2;
        $decPoint = ',';
        $thousandSep = ' ';

        $result = Utils::getformattedAmount(10050, $decimals, $decPoint, $thousandSep, 'HUF');
        $this->assertEquals('10 050', $result, 'Utils::getformattedAmount The amount format does not match.');

        $result2 = Utils::getformattedAmount(10050, $decimals, $decPoint, $thousandSep, 'EUR');
        $this->assertEquals('10 050,00', $result2, 'Utils::getformattedAmount The amount format does not match.');
    }
}
