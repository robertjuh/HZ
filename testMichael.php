<?php
/**
 * Testpagina voor EMont-parser.
 * @author: Michael Steenbeek
 */
require_once(__DIR__.'/php/php-emont/JSON_EMontParser.class.php');
require_once(__DIR__.'/php/SPARQLConnection.class.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>Testpagina voor EMont-parser</title>
	<meta charset='utf-8'>
</head>
<body>
<script src="js/jquery-2.1.3.js"></script>
<script src="js/d3.v3.js"></script>
<script src="js/cola.v3.min.js"></script>
<?php
$standaard_context_uri='emmwiki:Building_with_Nature-2Dinterventies_op_het_systeem';

$l1modellen=JSON_EMontParser::geefL1modellen();
$l2cases=JSON_EMontParser::geefL2cases();

echo '<form method="post" action="testMichael.php">L1: <select name="context">';

foreach ($l1modellen as $l1model)
{
	echo '<option ';
	if ($l1model->getUri()==$standaard_context_uri)
		echo 'selected="selected" ';
	echo 'value="'.$l1model->getUri().'">'.Uri::SMWuriNaarLeesbareTitel($l1model->getUri()).'</option>';
}
?>
</select><input type="submit" value="Opvragen" /></form>
<form method="post" action="testMichael.php">
L2: <select name="context">
<?php
foreach($l2cases as $l2case)
{
	echo '<option value="'.$l2case->getUri().'">'.Uri::SMWuriNaarLeesbareTitel($l2case->getUri()).'</option>';
}
?>
</select>
<input type="submit" value="Opvragen" />
</form>
<?php
if(!empty($_POST))
{
	$context_uri=$_POST['context'];
}
else
{
	$context_uri=$standaard_context_uri;
}

$connectie=new SPARQLConnection();

echo '<h1>Elementen uit de context: "'.Uri::SMWuriNaarLeesbareTitel($context_uri).'"</h1>';

$situatieparser=new JSON_EMontParser($context_uri);
$parse=$situatieparser->geefElementenInSituatie();
?>
<a href="#visualisatie">Spring naar de visualisatie</a>
<h2>Dump</h2>
<pre>
<?php var_dump($parse); ?>
</pre>
<style>

/* Standaard */
.node {
    fill: #eee;
    stroke: #000;
    stroke-width: 1px;
    cursor: move;
}
.nodeActivity {
    fill: #ede7dc;
}

.nodeCondition {
	fill: #4c97d6;
}

.nodeBelief {
	fill: #4c97d6;
}

.nodeGoal {
	fill: #ffffff;
}
.nodeOutcome {
	fill: #ffffff;
}

.link {
    stroke: #777;
    stroke-width: 2px;
    marker-end:#standaard;
}

.group {
  stroke: #fff;
  stroke-width: 1.5px;
  cursor: move;
  opacity: 0.7;
  color:#00FF00;
}

.label {
  	font-size:10px;
	font-family:Arial,Helvetica,sans-serif;
	fill:#000;
    cursor: move;
}

.labelBelief {
	fill: #fff;
}
</style>
<?php $svgheight=1280;$svgwidth=1600;$nodeheight=30;$nodewidth=100;?>

<a name="visualisatie"></a>
<h2>Visualisatie</h2>
<svg id="visualisatie" width="<?php echo $svgwidth;?>" height="<?php echo $svgheight;?>">
	<defs>
		<marker id="standaard" viewBox="0 -5 10 10" refX="10" refY="0" markerWidth="6" markerHeight="6" orient="auto">
        	<path d="M0,-5L10,0L0,5"></path>
    	</marker>
	</defs>
</svg>
<div id="dump"></div>
<script type="text/javascript">

	var graph;
    var color = d3.scale.category20();
	
	// Haal de gegevens op
	$.ajax({
		type : "POST",
		cache : false,
		url : "php/php-emont/VisualisationJSON.php",
		async : false,
		dataType: 'json',
		data:{ context_uri: "<?php echo $context_uri;?>"},
		success: function(result) {
			graph=result;
			tekenDiagram();
		}
	});

	function tekenDiagram()
	{
		var width = <?php echo $svgwidth;?>,
	    	height = <?php echo $svgheight;?>;

		// Selecteer de visualisatie-container
	    var svg = d3.select('#visualisatie');

		console.trace();	
		var force = cola.d3adaptor()
	    	.linkDistance(120)
	    	.avoidOverlaps(true)
			.size([width, height])
	        .handleDisconnected(false)
	    	.nodes(graph.nodes)
	    	.links(graph.links)
	    	.constraints(graph.constraints)
	    	.groups(graph.groups);
	    var margin = 5, pad = 10;

		// Teken de pijlen
		force.on("tick", function () {
			 node.each(function (d) {
	                d.innerBounds = d.bounds.inflate(- margin);
	            });
	            link.each(function (d) {
	                cola.vpsc.makeEdgeBetween(d, d.source.innerBounds, d.target.innerBounds, 0);
	            });

	            link.attr("x1", function (d) { return d.sourceIntersection.x; })
	                .attr("y1", function (d) { return d.sourceIntersection.y; })
	                .attr("x2", function (d) { return d.arrowStart.x; })
	                .attr("y2", function (d) { return d.arrowStart.y; });

	            label.each(function (d) {
	                var b = this.getBBox();
	                d.width = b.width + 2 * margin + 8;
	                d.height = b.height + 2 * margin + 8;
	            });

	            node.attr("x", function (d) { return d.innerBounds.x; })
	                .attr("y", function (d) { return d.innerBounds.y; })
	                .attr("width", function (d) { return d.innerBounds.width(); })
	                .attr("height", function (d) { return d.innerBounds.height(); });

	            group.attr("x", function (d) { return d.bounds.x; })
	                 .attr("y", function (d) { return d.bounds.y; })
	                .attr("width", function (d) { return d.bounds.width(); })
	                .attr("height", function (d) { return d.bounds.height(); });

	            //groupTitles.attr("x",function(d,i) {return group(i).bounds.x+10;});

	            label.attr("transform", function (d) {
	                return "translate(" + (d.x + margin - d.width/2) + "," + (d.y + margin - d.height/2) + ")";
	            });
		});

		// De force layout zet alles automatisch op zijn plek
		force.start();

	    var group = svg.selectAll(".group")
	        .data(graph.groups)
	       .enter().append("rect")
	        .attr("rx", 10).attr("ry", 10)
	        .attr("class", "group")
	        .attr('width',<?php echo $nodewidth;?>)
		    .attr('height',<?php echo $nodeheight;?>)
	        .style("fill", function (d, i) { return color(i); })

	    /*var groupTitles = svg.selectAll(".groupTitles")
	    	 .data(graph.groups)
	        .enter().append("rect")
     	     .attr("class", "groupTitles")
	         .attr('width',100)
	         .attr('height',15)
	         .style("fill","#ff0000");*/

	    var link = svg.selectAll(".link")
	        .data(graph.links)
	       .enter().append("line")
	        .attr("class", "link")
	        .attr("marker-end", "url(#standaard)")
	        .attr("title", function (d) { var title=d.type; if(d.extraInfo!=null){title=title+d.extraInfo}if(d.note!=null){title=title+"\n"+d.note}return title;});

	    var node = svg.selectAll(".node")
	         .data(graph.nodes)
	       .enter().append("rect")
	         .attr("class", function (d) {return "node node"+d.type})
	           .attr("rx", 5).attr("ry", 5)
	           .attr('width',<?php echo $nodewidth;?>)
		       .attr('height',<?php echo $nodeheight;?>)
	         .call(force.drag);

		// Titels
	    var label = svg.selectAll(".label")
	        .data(graph.nodes)
	        .enter().append("text")
	         .attr("class", function (d) {return "label label"+d.type})
	         .text(function (d) { return d.heading; })
		     .call(force.drag);

	    node.append("title")
	        .text(function (d) { return d.heading; });

	    var insertLinebreaks = function (d) {
	        var el = d3.select(this);
	        var words = d.heading.split(' ');
	        el.text('');

	        for (var i = 0; i < words.length; i++) {
	            var tspan = el.append('tspan').text(words[i]);
	            tspan.attr('x', margin).attr('dy', 15)
	                 .attr("font-size", "12")
	                 .attr("style","fill:inherit;");
	        }
	    };
		label.each(insertLinebreaks);
	}
</script>
</body>
</html>