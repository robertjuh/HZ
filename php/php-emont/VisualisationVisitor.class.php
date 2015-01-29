<?php
/**
 * Haalt de elementen uit een bepaalde Situatie op en geeft ze terug als JSON voor de visualisatie.
 * @author Michael Steenbeek
 */
require_once(__DIR__.'/PHPEMontVisitor.interface.php');
require_once(__DIR__.'/IntentionalElement.class.php');
require_once(__DIR__.'/Activity.class.php');
require_once(__DIR__.'/Context.class.php');

class VisualisationVisitor implements PHPEMontVisitor
{
	function __construct() {}
	
	function visit($visitee)
	{
		if ($visitee instanceof IntentionalElement)
		{
			$uri=$visitee->getUri();
			$node=array();
			$node['type']=get_class($visitee);
			$node['heading']=$visitee->getHeading();
			$node['decompositionType']=$visitee->getDecompositionType();

			$links=array();
			$ies_contexten=array();
			foreach($visitee->getInstanceOf() as $link)
			{
				$links[]=array('source'=>$uri,'type'=>'instanceOf','target'=>$link->getUri());
			}
			foreach($visitee->getPartOf() as $link)
			{
				$links[]=array('source'=>$uri,'type'=>'partOf','target'=>$link->getUri());
			}
			foreach($visitee->getContributes() as $link)
			{
				$links[]=array('source'=>$uri,'type'=>'contributes','target'=>$link->getLink()->getUri(),'note'=>$link->getLinkNote(),'contributionValue'=>$link->getContributionValue());
			}
			foreach($visitee->getDepends() as $link)
			{
				$links[]=array('source'=>$uri,'type'=>'depends','target'=>$link->getLink()->getUri(),'note'=>$link->getLinkNote());
			}
			foreach($visitee->getContext() as $link)
			{
				$ies_contexten[]=array('ie'=>$uri,'context'=>$link->getUri());
			}
			if($visitee instanceOf Activity)
			{
				foreach($visitee->getConnects() as $link)
				{
					$links[]=array('source'=>$uri,'type'=>'connects','target'=>$link->getLink()->getUri(),'note'=>$link->getLinkNote(),'condition'=>$link->getLinkCondition(),'connectionType'=>$link->getConnectionType());
				}
			}
			
			$return['node']=$node;
			$return['links']=$links;
			$return['ies_contexten']=$ies_contexten;
			return $return;
		}
		elseif($visitee instanceof Context)
		{
			$context=array();
			$uri=$visitee->getUri();
			$context['description']=$visitee->getDescription();
			$contextLinks=array();
			foreach($visitee->getSupercontext() as $link)
			{
				$contextLinks[]=array('source'=>$uri,'target'=>$link->getUri());
			}
			$return['context']=$context;
			$return['contextLinks']=$contextLinks;
			return $return;
		}
	}
}