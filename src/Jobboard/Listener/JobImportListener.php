<?php
/**
 * YAWIK Jobboard
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace Jobboard\Listener;

use Core\Entity\Collection\ArrayCollection;
use Core\Entity\Tree\NodeInterface;
use Core\Exception\MissingDependencyException;
use Core\Form\Hydrator\Strategy\TreeSelectStrategy;
use Jobs\Listener\Events\JobEvent;
use Zend\Filter\FilterInterface;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class JobImportListener 
{
    /**
     *
     *
     * @var NodeInterface
     */
    private $employmentTypes;

    /**
     *
     *
     * @var FilterInterface
     */
    private $employmentTypesFilter;

    /**
     *
     *
     * @var NodeInterface
     */
    private $industries;

    /**
     *
     *
     * @var FilterInterface
     */
    private $industriesFilter;



    public function __construct(array $options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = "set$name";

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getEmploymentTypes()
    {
        if (!$this->employmentTypes) {
            throw new MissingDependencyException('employmentTypes', $this);
        }

        return $this->employmentTypes;
    }

    /**
     * @param NodeInterface $employmentTypes
     *
     * @return self
     */
    public function setEmploymentTypes($employmentTypes)
    {
        $this->employmentTypes = $employmentTypes;

        return $this;
    }

    /**
     * @return FilterInterface
     */
    public function getEmploymentTypesFilter()
    {
        if (!$this->employmentTypesFilter) {
            $this->setEmploymentTypesFilter(new \Zend\Filter\Callback(function() { return []; }));
        }

        return $this->employmentTypesFilter;
    }

    /**
     * @param FilterInterface $employmentTypesFilter
     *
     * @return self
     */
    public function setEmploymentTypesFilter($employmentTypesFilter)
    {
        $this->employmentTypesFilter = $employmentTypesFilter;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getIndustries()
    {
        if (!$this->industries) {
            throw new MissingDependencyException('industries', $this);
        }

        return $this->industries;
    }

    /**
     * @param NodeInterface $industries
     *
     * @return self
     */
    public function setIndustries($industries)
    {
        $this->industries = $industries;

        return $this;
    }

    /**
     * @return FilterInterface
     */
    public function getIndustriesFilter()
    {
        if (!$this->industriesFilter) {
            $this->setIndustriesFilter(new \Zend\Filter\Callback(function() { return []; }));
        }

        return $this->industriesFilter;
    }

    /**
     * @param FilterInterface $industriesFilter
     *
     * @return self
     */
    public function setIndustriesFilter($industriesFilter)
    {
        $this->industriesFilter = $industriesFilter;

        return $this;
    }



    public function __invoke(JobEvent $event)
    {
        $job = $event->getJobEntity();
        $jobClassifications = $job->getClassifications();
        $strategy = new TreeSelectStrategy();
        $strategy->setShouldCreateLeafs(true);

        $positions = $event->getParam('position');

        if ($positions) {

            $strategy->setAllowSelectMultipleItems(false)
                     ->setAttachedLeafs($jobClassifications->getEmploymentTypes())
                     ->setTreeRoot($this->getEmploymentTypes())
                     ->hydrate($this->getEmploymentTypesFilter()->filter($positions));
        }

        $branches = $event->getParam('branches');

        if ($branches) {
            $strategy->setAllowSelectMultipleItems(true)
                     ->setAttachedLeafs($jobClassifications->getIndustries())
                     ->setTreeRoot($this->getIndustries())
                     ->hydrate($this->getIndustriesFilter()->filter($branches));
        }
    }
}
