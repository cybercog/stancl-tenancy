<?php

declare(strict_types=1);

namespace Stancl\Tenancy\Tests;

use Mockery;
use Stancl\Tenancy\Contracts\StorageDriver;
use Stancl\Tenancy\Tenant;
use Tenancy;

class TenantClassTest extends TestCase
{
    public $autoInitTenancy = false;
    public $autoCreateTenant = false;

    /** @test */
    public function data_cache_works_properly()
    {
        // $spy = Mockery::spy(config('tenancy.storage_driver'))->makePartial();
        // $this->instance(StorageDriver::class, $spy);

        $tenant = Tenant::create(['foo.localhost'], ['foo' => 'bar']);
        $this->assertSame('bar', $tenant->data['foo']);

        $tenant->put('abc', 'xyz');
        $this->assertSame('xyz', $tenant->data['abc']);

        $tenant->put(['aaa' => 'bbb', 'ccc' => 'ddd']);
        $this->assertSame('bbb', $tenant->data['aaa']);
        $this->assertSame('ddd', $tenant->data['ccc']);

        // $spy->shouldNotHaveReceived('get');

        $this->assertSame(null, $tenant->dfuighdfuigfhdui);
        // $spy->shouldHaveReceived('get')->once();

        Mockery::close();
    }

    /** @test */
    public function tenant_can_have_multiple_domains()
    {
        $tenant = Tenant::create(['foo.localhost', 'bar.localhost']);
        $this->assertSame(['foo.localhost', 'bar.localhost'], $tenant->domains);
        $this->assertSame($tenant->id, Tenancy::findByDomain('foo.localhost')->id);
        $this->assertSame($tenant->id, Tenancy::findByDomain('bar.localhost')->id);
    }

    /** @test */
    public function updating_a_tenant_works()
    {
        $id = 'abc' . $this->randomString();
        $tenant = Tenant::create(['foo.localhost'], ['id' => $id]);
        $tenant->foo = 'bar';
        $tenant->save();
        $this->assertEquals(['id' => $id, 'foo' => 'bar'], $tenant->data);
        $this->assertEquals(['id' => $id, 'foo' => 'bar'], tenancy()->find($id)->data);

        $tenant->addDomains('abc.localhost');
        $tenant->save();
        $this->assertEqualsCanonicalizing(['foo.localhost', 'abc.localhost'], $tenant->domains);
        $this->assertEqualsCanonicalizing(['foo.localhost', 'abc.localhost'], tenancy()->find($id)->domains);

        $tenant->removeDomains(['foo.localhost']);
        $tenant->save();
        $this->assertEqualsCanonicalizing(['abc.localhost'], $tenant->domains);
        $this->assertEqualsCanonicalizing(['abc.localhost'], tenancy()->find($id)->domains);

        $tenant->withDomains(['completely.localhost', 'different.localhost', 'domains.localhost']);
        $tenant->save();
        $this->assertEqualsCanonicalizing(['completely.localhost', 'different.localhost', 'domains.localhost'], $tenant->domains);
        $this->assertEqualsCanonicalizing(['completely.localhost', 'different.localhost', 'domains.localhost'], tenancy()->find($id)->domains);
    }

    /** @test */
    public function with_methods_work()
    {
        $id = 'foo' . $this->randomString();
        $tenant = Tenant::new()->withDomains(['foo.localhost'])->with('id', $id);
        $this->assertSame($id, $tenant->id);

        $id2 = 'bar' . $this->randomString();
        $tenant2 = Tenant::new()->withDomains(['bar.localhost'])->withId($id2)->withFooBar('xyz');
        $this->assertSame($id2, $tenant2->data['id']);
        $this->assertSame('xyz', $tenant2->foo_bar);
        $this->assertArrayHasKey('foo_bar', $tenant2->data);
    }

    /** @test */
    public function an_exception_is_thrown_when_an_unknown_method_is_called()
    {
        $tenant = Tenant::new();
        $this->expectException(\BadMethodCallException::class);
        $tenant->sdjigndfgnjdfgj();
    }
}
