import * as d3 from "https://cdn.jsdelivr.net/npm/d3@7";

// TODO : Remove disable
/* eslint-disable no-console */

export const displayTeams = () => {

    const generatedTeamsDiv = document.getElementById("generatedteams");
    let cognitiveModes = ["EF", "EN", "ES", "ET", "IF", "IN", "IS", "IT"];
    const generatedTeamsRaw = JSON.parse(generatedTeamsDiv.dataset.generatedteams);
    const generatedTeams = [
        cognitiveModes.reduce((obj, mode) => {
            obj[mode] = 1 / cognitiveModes.length * 100;
            return obj;
        }, {}),
        ...generatedTeamsRaw.map(team => {
            const counter = team['cognitive_modes_counter'];
            const cognitiveModesCount = Object.entries(counter)
                /* eslint-disable-next-line no-unused-vars */
                .reduce((sum, [_, modeCount]) => sum + modeCount, 0);
            return cognitiveModes.reduce((obj, mode) => {
                obj[mode] = counter[mode] / cognitiveModesCount * 100;
                return obj;
            }, {});
        })
    ];
    const matchingStrategies = ['Ideal', ...new Set(generatedTeamsRaw.map(team => team['matching_strategy']))];

    // Stacked bar chart inspired from https://d3-graph-gallery.com/graph/barplot_stacked_basicWide.html

    // Declare the chart dimensions and margins.
    // set the dimensions and margins of the graph
    const parentWidth = document.getElementById("polyteamgeneratedteams").getBoundingClientRect().width;
    // const barsWidth = Math.min(
    //     1000,
    //     Math.max(600, 25 * generatedTeams.length + (cognitiveModes.length - 1) * 5)
    // );
    const margin = {top: 20, right: 50, bottom: 50, left: 50},
        width = parentWidth - margin.left - margin.right, // TODO: Adjust width with bars
        height = 400 - margin.top - margin.bottom;

    // append the svg object to the body of the page
    let svg = d3.select("#polyteamgeneratedteams")
        .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform",
            "translate(" + margin.left + "," + margin.top + ")");

    // TODO : Internationalisation
    const labels = generatedTeams.map((_, index) => index === 0 ? "Ideal" : `${index}`);

    // Legend
    // Append legend items to SVG
    const markersColors = d3.scaleOrdinal()
        .domain(matchingStrategies)
        .range(["burlywood", "black", "chocolate"]);
    var legend = svg.selectAll(".legend")
        .data(matchingStrategies)
        .enter()
        .append("g")
        .attr("class", "legend");

    legend.append("circle")
        .attr("cx", 20)
        .attr("cy", 10)
        .attr("r", 6)
        .style("fill", function (d) {
            return markersColors(d);
        });

    legend.append("text")
        .attr("x", 40)
        .attr("y", 10)
        .attr("dy", ".35em")
        .text(function (d) {
            return d;
        })
        .style("fill", function (d) {
            return markersColors(d);
        });

    // Calculate total width of all legend items
    var legendItems = document.querySelectorAll(".legend");
    var totalWidth = 0;
    legendItems.forEach(function (item) {
        totalWidth += item.getBBox().width;
    });

    // Define padding between legend items
    var padding = 40;

    // Adjust position of legend items
    var startX = (width - totalWidth - padding * (matchingStrategies.length - 1)) / 2;
    var previousWidth = 0;
    legend.attr("transform", function (d, i) {
        previousWidth += i === 0 ? 0 : legendItems[i - 1].getBBox().width;
        var xPos = startX + previousWidth + (i * padding);
        return "translate(" + xPos + `, ${-margin.top})`;
    });

    // Add X axis
    let x = d3.scaleBand()
        .domain(labels)
        .range([0, width])
        .padding([0.2]);
    svg.append("g")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x).tickSizeOuter(0));

    // Add X axis label:
    svg.append("text")
        .attr("text-anchor", "end")
        .attr("x", (width + margin.left + margin.right) / 2)
        .attr("y", height + margin.bottom * 0.75)
        .attr("font-size", "medium")
        .text("Teams"); // TODO: Internationalization

    // Add Y left axis
    let yLeft = d3.scaleLinear()
        .domain([0, 100])
        .range([height, 0]);
    svg.append("g")
        .call(d3.axisLeft(yLeft));

    // Y left axis label:
    svg.append("text")
        .attr("text-anchor", "middle")
        .attr("transform", "rotate(-90)")
        .attr("y", -margin.left * 0.75)
        .attr("x", -height / 2)
        .attr("font-size", "medium")
        .text("Cognitive variances (%)"); // TODO: Internationalization

    // Add Y right axis
    const cognitiveVariances = [
        0,
        ...generatedTeamsRaw.map(t => t["cognitive_variance"])
    ];
    let yRight = d3.scaleLinear()
        .domain([0, Math.max(12, Math.max(...cognitiveVariances) + 1)])
        .range([height, 0]);
    svg.append("g")
        .attr("transform", `translate(${width}, 0)`)
        .call(d3.axisRight(yRight));

    // Y right axis label:
    svg.append("text")
        .attr("text-anchor", "middle")
        .attr("transform", "rotate(-90)")
        .attr("y", width + margin.left * 0.75)
        .attr("x", -height / 2)
        .attr("font-size", "medium")
        .text("Standard deviation (markers)"); // TODO: Internationalization

    // color palette = one color per subgroup
    let color = d3.scaleOrdinal()
        .domain(cognitiveModes)
        .range(['#1F77B4', '#FF7F0E', '#2CA02C', '#D62728', '#9467BD', '#8C564B', '#E377C2', '#7F7F7F']);

    // stack the data? --> stack per subgroup
    let stackedData = d3.stack().keys(cognitiveModes)(generatedTeams);

    // Show the bars
    svg.append("g")
        .selectAll("g")
        // Enter the stack data = loop key per key = group per group
        .data(stackedData)
        .enter().append("g")
        .attr("fill", function (d) {
            return color(d.key);
        })
        .selectAll("rect")
        // enter a second time = loop subgroup per subgroup to add all rectangles
        .data(function (d) {
            return d;
        })
        .enter()
        .append("rect")
        .attr("x", function (d, i) {
            return x(labels[i]);
        })
        .attr("y", function (d) {
            return yLeft(d[1]);
        })
        .attr("height", function (d) {
            return yLeft(d[0]) - yLeft(d[1]);
        })
        .attr("width", x.bandwidth());

    svg.append("g")
        .selectAll("g")
        // Enter the stack data = loop key per key = group per group
        .data(cognitiveModes)
        .enter()
        .append("text")
        .text(function (d) {
            return d;
        })
        .attr("text-anchor", "middle")
        .attr("dominant-baseline", "middle")
        .attr("alignment-baseline", "middle")
        .attr("x", function () {
            return x(labels[0]) + x.bandwidth() / 2;
        })
        .attr("y", function (_, i) {
            return (yLeft(stackedData[i][0][0]) + yLeft(stackedData[i][0][1])) / 2;
        })
        .attr("font-weight", "bold")
        .attr("font-size", `${Math.min(x.bandwidth() / 2, 35)}px`)
        .attr("fill", "white");

    svg.append("g")
        .selectAll("dot")
        // Enter the stack data = loop key per key = group per group
        .data(cognitiveVariances)
        .enter()
        .append("circle")
        .attr("cx", function (_, i) {
            return x(labels[i]) + x.bandwidth() / 2;
        })
        .attr("cy", function (d) {
            return yRight(d);
        })
        .attr("r", Math.min(5, x.bandwidth() * 0.45))
        .style("fill", function (_, i) {
            if (i === 0) {
                return markersColors("Ideal");
            }
            return markersColors(generatedTeamsRaw[i - 1]["matching_strategy"]);
        });

};
