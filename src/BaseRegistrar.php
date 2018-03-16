<?php

namespace Sayla\Support\Bindings;

abstract class BaseRegistrar implements Registrar
{
    use RegistrarTrait;
    private $useSingletons = false;
    private $abstracts = [];
    private $aliases = [];

    public function boot(BindingProvider ...$providers)
    {
        foreach ($providers as $provider)
            foreach ($this->getIncludedBindingAliases($provider) as $alias) {
                $this->bootProvider($provider, $alias);
            }
    }

    public function register(BindingProvider ...$providers)
    {
        foreach ($providers as $provider)
            foreach ($this->getIncludedBindingAliases($provider) as $alias) {
                $this->registerProvider($provider, $alias);
            }
    }

    /**
     * @param $provider
     * @param $alias
     */
    protected function bootProvider(BindingProvider $provider, string $alias): void
    {
        $booter = $provider->getBooter($alias);
        if ($booter != null) {
            $this->callBooter($booter, $this->aliasPrefix . $alias);
        }
    }

    protected function callBooter(callable $booter, string $qualifiedAlias)
    {
        $booter($qualifiedAlias);
    }

    /**
     * @param \Sayla\Support\Bindings\BindingProvider $provider
     * @param string $alias
     */
    protected function registerProvider(BindingProvider $provider, string $alias): void
    {
        $this->abstracts[] = $abstract = $provider->getBindingName($alias) ?? $alias;
        $this->aliases[] = $containerAlias = $this->aliasPrefix . $alias;
        $resolver = $provider->getResolver($alias);
        $isSingleton = $provider->isSingleton($alias);
        if (!$isSingleton && $this->useSingletons) {
            $isSingleton = $this->useSingletons;
        }
        $this->registerBinding($isSingleton, $abstract, $resolver, $containerAlias);
        $this->afterBind($abstract, $containerAlias);
    }

    /**
     * @param bool $isSingleton
     * @param string $abstract
     * @param null|string $resolver
     * @param null|string $alias
     * @return mixed
     */
    protected abstract function registerBinding(bool $isSingleton, string $abstract, $resolver = null, ?string $alias);

    /**
     * @param string $abstract
     * @param null|string $alias
     */
    protected function afterBind(string $abstract, ?string $alias)
    {

    }

    /**
     * @param bool $useSingletons
     * @return $this
     */
    public function useSingletons(bool $useSingletons = true)
    {
        $this->useSingletons = $useSingletons;
        return $this;
    }

}