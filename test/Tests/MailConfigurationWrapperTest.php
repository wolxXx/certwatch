<?php

class MailConfigurationWrapperTest extends \Certwatch\Test\TestBase
{
    private string $testConfigPath;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->testConfigPath = __DIR__ . '/fixture/mail-config-test.php';
        
        // Ensure the fixture directory exists
        if (!is_dir(__DIR__ . '/fixture')) {
            mkdir(__DIR__ . '/fixture', 0777, true);
        }
    }
    
    public function tearDown(): void
    {
        // Clean up test config file if it exists
        if (file_exists($this->testConfigPath)) {
            unlink($this->testConfigPath);
        }
        
        parent::tearDown();
    }
    
    /**
     * Test instantiation and default configuration path
     */
    public function testInstantiation()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $this->assertInstanceOf(\Certwatch\MailConfigurationWrapper::class, $instance);
        
        $expectedPath = realpath(__DIR__ . '/../../src') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mail-config.php';
        $this->assertSame($expectedPath, $instance->getPathToConfigurationFile());
    }
    
    /**
     * Test setting and getting the configuration file path
     */
    public function testGetSetPathToConfigurationFile()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $testPath = '/path/to/config.php';
        
        $result = $instance->setPathToConfigurationFile($testPath);
        
        $this->assertSame($testPath, $instance->getPathToConfigurationFile());
        $this->assertSame($instance, $result, 'Setter should return instance for method chaining');
    }
    
    /**
     * Test init method throws exception when config file doesn't exist
     */
    public function testInitThrowsExceptionWhenFileNotFound()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $nonExistentPath = '/path/to/nonexistent/config.php';
        $instance->setPathToConfigurationFile($nonExistentPath);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('could not find mail config under "' . $nonExistentPath . '"!');
        
        $instance->init();
    }
    
    /**
     * Test init method throws exception when config file doesn't return an array
     */
    public function testInitThrowsExceptionWhenConfigIsNotArray()
    {
        // Create a test config file that returns a string instead of an array
        file_put_contents($this->testConfigPath, '<?php return "not an array";');
        
        $instance = new \Certwatch\MailConfigurationWrapper();
        $instance->setPathToConfigurationFile($this->testConfigPath);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('content in config file under "' . $this->testConfigPath . '" is not an array!');
        
        $instance->init();
    }
    
    /**
     * Test init method throws exception when required keys are missing
     */
    public function testInitThrowsExceptionWhenRequiredKeysAreMissing()
    {
        // Create a test config file with missing required keys
        file_put_contents($this->testConfigPath, '<?php return ["username" => "test", "password" => "test"];');
        
        $instance = new \Certwatch\MailConfigurationWrapper();
        $instance->setPathToConfigurationFile($this->testConfigPath);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('content in config file under "' . $this->testConfigPath . '" is missing the keys server, encryption, port, from, to');
        
        $instance->init();
    }
    
    /**
     * Test init method with valid configuration
     */
    public function testInitWithValidConfiguration()
    {
        // Create a valid test config file
        $config = [
            'username' => 'test@example.com',
            'password' => 'password123',
            'server' => 'smtp.example.com',
            'encryption' => 'tls',
            'port' => 587,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com',
            'fromName' => 'Test Sender',
            'bcc' => ['bcc1@example.com', 'bcc2@example.com'],
            'cc' => 'cc@example.com'
        ];
        
        file_put_contents($this->testConfigPath, '<?php return ' . var_export($config, true) . ';');
        
        $instance = new \Certwatch\MailConfigurationWrapper();
        $instance->setPathToConfigurationFile($this->testConfigPath);
        
        $result = $instance->init();
        
        // Verify the instance was properly initialized
        $this->assertSame($instance, $result);
        $this->assertSame($config['username'], $instance->getUsername());
        $this->assertSame($config['password'], $instance->getPassword());
        $this->assertSame($config['server'], $instance->getServer());
        $this->assertSame($config['encryption'], $instance->getEncryption());
        $this->assertSame($config['port'], $instance->getPort());
        $this->assertSame($config['from'], $instance->getFrom());
        $this->assertSame($config['fromName'], $instance->getFromName());
        $this->assertSame((array)$config['to'], $instance->getTo());
        $this->assertSame($config['bcc'], $instance->getBcc());
        $this->assertSame((array)$config['cc'], $instance->getCc());
    }
    
    /**
     * Test init method with minimal valid configuration (without optional fields)
     */
    public function testInitWithMinimalValidConfiguration()
    {
        // Create a minimal valid test config file (without optional fields)
        $config = [
            'username' => 'test@example.com',
            'password' => 'password123',
            'server' => 'smtp.example.com',
            'encryption' => 'tls',
            'port' => 587,
            'from' => 'sender@example.com',
            'to' => 'recipient@example.com'
        ];
        
        file_put_contents($this->testConfigPath, '<?php return ' . var_export($config, true) . ';');
        
        $instance = new \Certwatch\MailConfigurationWrapper();
        $instance->setPathToConfigurationFile($this->testConfigPath);
        
        $result = $instance->init();
        
        // Verify the instance was properly initialized with default values for optional fields
        $this->assertSame($instance, $result);
        $this->assertSame($config['username'], $instance->getUsername());
        $this->assertSame($config['password'], $instance->getPassword());
        $this->assertSame($config['server'], $instance->getServer());
        $this->assertSame($config['encryption'], $instance->getEncryption());
        $this->assertSame($config['port'], $instance->getPort());
        $this->assertSame($config['from'], $instance->getFrom());
        $this->assertSame((array)$config['to'], $instance->getTo());
        $this->assertSame([], $instance->getBcc());
        $this->assertSame([], $instance->getCc());
    }
    
    /**
     * Test username getter and setter
     */
    public function testGetSetUsername()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'test@example.com';
        
        $result = $instance->setUsername($value);
        
        $this->assertSame($value, $instance->getUsername());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test password getter and setter
     */
    public function testGetSetPassword()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'securePassword123';
        
        $result = $instance->setPassword($value);
        
        $this->assertSame($value, $instance->getPassword());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test server getter and setter
     */
    public function testGetSetServer()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'smtp.example.com';
        
        $result = $instance->setServer($value);
        
        $this->assertSame($value, $instance->getServer());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test encryption getter and setter
     */
    public function testGetSetEncryption()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'ssl';
        
        $result = $instance->setEncryption($value);
        
        $this->assertSame($value, $instance->getEncryption());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test port getter and setter
     */
    public function testGetSetPort()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 465;
        
        $result = $instance->setPort($value);
        
        $this->assertSame($value, $instance->getPort());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test from getter and setter
     */
    public function testGetSetFrom()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'sender@example.com';
        
        $result = $instance->setFrom($value);
        
        $this->assertSame($value, $instance->getFrom());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test fromName getter and setter
     */
    public function testGetSetFromName()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'Test Sender';
        
        $result = $instance->setFromName($value);
        
        $this->assertSame($value, $instance->getFromName());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test to getter and setter with string value
     */
    public function testGetSetToWithString()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'recipient@example.com';
        
        $result = $instance->setTo($value);
        
        $this->assertSame(['recipient@example.com'], $instance->getTo());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test to getter and setter with array value
     */
    public function testGetSetToWithArray()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = ['recipient1@example.com', 'recipient2@example.com'];
        
        $result = $instance->setTo($value);
        
        $this->assertSame($value, $instance->getTo());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test bcc getter and setter with string value
     */
    public function testGetSetBccWithString()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'bcc@example.com';
        
        $result = $instance->setBcc($value);
        
        $this->assertSame(['bcc@example.com'], $instance->getBcc());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test bcc getter and setter with array value
     */
    public function testGetSetBccWithArray()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = ['bcc1@example.com', 'bcc2@example.com'];
        
        $result = $instance->setBcc($value);
        
        $this->assertSame($value, $instance->getBcc());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test cc getter and setter with string value
     */
    public function testGetSetCcWithString()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = 'cc@example.com';
        
        $result = $instance->setCc($value);
        
        $this->assertSame(['cc@example.com'], $instance->getCc());
        $this->assertSame($instance, $result);
    }
    
    /**
     * Test cc getter and setter with array value
     */
    public function testGetSetCcWithArray()
    {
        $instance = new \Certwatch\MailConfigurationWrapper();
        $value = ['cc1@example.com', 'cc2@example.com'];
        
        $result = $instance->setCc($value);
        
        $this->assertSame($value, $instance->getCc());
        $this->assertSame($instance, $result);
    }
}