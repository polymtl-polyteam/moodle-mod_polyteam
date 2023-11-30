import * as d3 from 'https://cdn.jsdelivr.net/npm/d3@7';

// Create a stacked bar chart inspired from https://d3-graph-gallery.com/graph/barplot_stacked_basicWide.html
export const displayTeams = () => {

    // Get generated teams data and internationalized strings
    const dataDiv = document.getElementById('generatedteams');
    const strings = JSON.parse(dataDiv.dataset.strings);
    const generatedTeamsRaw = JSON.parse(dataDiv.dataset.generatedteams);
    const allCognitiveModes = JSON.parse(dataDiv.dataset.allcognitivemodes);
    const generatedTeams = [
        allCognitiveModes.reduce((obj, mode) => {
            obj[mode] = 1 / allCognitiveModes.length * 100;
            return obj;
        }, {}),
        ...generatedTeamsRaw.map(team => {
            const counter = team['cognitive_modes_counter'];
            const cognitiveModesCount = Object.entries(counter)
                /* eslint-disable-next-line no-unused-vars */
                .reduce((sum, [_, modeCount]) => sum + modeCount, 0);
            return allCognitiveModes.reduce((obj, mode) => {
                if (cognitiveModesCount === 0) {
                    obj[mode] = 0;
                } else {
                    obj[mode] = counter[mode] / cognitiveModesCount * 100;
                }
                return obj;
            }, {});
        })
    ];

    const matchingStrategies = ['ideal', ...new Set(generatedTeamsRaw.map(team => team['matching_strategy']))];

    // Declare the chart dimensions and margins.
    const parentWidth = document.getElementById('polyteamgeneratedteams').getBoundingClientRect().width;
    const margin = {top: 50, right: 50, bottom: 50, left: 50},
        width = parentWidth - margin.left - margin.right,
        height = 400 - margin.top - margin.bottom;
    // Append the svg object to the page and set the dimensions and margins of the graph
    let svg = d3.select('#polyteamgeneratedteams')
        .append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom)
        .append('g')
        .attr('transform',
            'translate(' + margin.left + ',' + margin.top + ')');

    // Append legend items to SVG
    const markersColors = d3.scaleOrdinal()
        .domain(matchingStrategies)
        // .range(['burlywood', 'black', 'chocolate']);
        .range(d3.schemeDark2);
    const legend = svg.selectAll('.legend')
        .data(matchingStrategies)
        .enter()
        .append('g')
        .attr('class', 'legend');
    legend.append('rect')
        .attr('x', 20)
        .attr('y', 4)
        .attr('width', 12)
        .attr('height', 12)
        .attr('fill', 'none')
        .attr('stroke-width', 1.5)
        .style('stroke', (d) => markersColors(d));
    legend.append('text')
        .attr('x', 40)
        .attr('y', 10)
        .attr('dy', '.35em')
        .text((d) => strings[d])
        .style('fill', (d) => markersColors(d));
    // Calculate total width of all legend items
    const legendItems = document.querySelectorAll('.legend');
    let totalWidth = 0;
    legendItems.forEach(function (item) {
        totalWidth += item.getBBox().width;
    });
    const legendItemsPadding = 20;
    // Adjust position of legend items
    const startX = (width - totalWidth - legendItemsPadding * (matchingStrategies.length - 1)) / 2;
    let previousWidth = 0;
    legend.attr('transform', function (d, i) {
        previousWidth += i === 0 ? 0 : legendItems[i - 1].getBBox().width;
        const xPos = startX + previousWidth + (i * legendItemsPadding);
        return 'translate(' + xPos + `, ${-margin.top * 0.75})`;
    });

    // Add X axis and labels
    const labels = generatedTeams.map((_, index) => index === 0 ? strings['ideal'] : `${index}`);
    const x = d3.scaleBand()
        .domain(labels)
        .range([0, width])
        .padding([0.2]);
    svg.append('g')
        .attr('transform', 'translate(0,' + height + ')')
        .call(d3.axisBottom(x).tickSizeOuter(0));
    svg.append('text')
        .attr('text-anchor', 'end')
        .attr('x', (width + margin.left + margin.right) / 2)
        .attr('y', height + margin.bottom * 0.75)
        .attr('font-size', 'medium')
        .text(strings['teams']);

    // Add Y left axis (cognitive modes proportions) and labels
    let yLeft = d3.scaleLinear()
        .domain([0, 100])
        .range([height, 0]);
    svg.append('g')
        .call(d3.axisLeft(yLeft));
    svg.append('text')
        .attr('text-anchor', 'middle')
        .attr('transform', 'rotate(-90)')
        .attr('y', -margin.left * 0.75)
        .attr('x', -height / 2)
        .attr('font-size', 'medium')
        .text(strings['cognitivemodesproportions']);

    // Add Y right axis and labels
    const cognitiveVariances = [
        0,
        ...generatedTeamsRaw.map(t => t['cognitive_variance'])
    ];
    let yRight = d3.scaleLinear()
        .domain([0, Math.max(12, Math.max(...cognitiveVariances) + 1)])
        .range([height, 0]);
    svg.append('g')
        .attr('transform', `translate(${width}, 0)`)
        .call(d3.axisRight(yRight));
    svg.append('text')
        .attr('text-anchor', 'middle')
        .attr('transform', 'rotate(-90)')
        .attr('y', width + margin.left * 0.75)
        .attr('x', -height / 2)
        .attr('font-size', 'medium')
        .text(strings['standarddeviation']);

    // Show the bars
    // color palette = one color per subgroup
    let color = d3.scaleOrdinal()
        .domain(allCognitiveModes)
        .range(['#1F77B4', '#FF7F0E', '#2CA02C', '#D62728', '#9467BD', '#8C564B', '#E377C2', '#7F7F7F']);
    let stackedData = d3.stack().keys(allCognitiveModes)(generatedTeams);
    svg.append('g')
        .selectAll('g')
        // Enter the stack data = loop key per key = group per group
        .data(stackedData)
        .enter().append('g')
        .attr('fill', (d) => color(d.key))
        .selectAll('rect')
        // enter a second time = loop subgroup per subgroup to add all rectangles
        .data(d => d)
        .enter()
        .append('rect')
        .attr('x', (_, i) => x(labels[i]))
        .attr('y', (d) => yLeft(d[1]))
        .attr('height', (d) => yLeft(d[0]) - yLeft(d[1]))
        .attr('width', x.bandwidth());
    svg.append('g')
        .selectAll('rect')
        // Enter the stack data = loop key per key = group per group
        .data(generatedTeams)
        .enter()
        .append('rect')
        .attr('stroke', (_, i) => {
            if (i === 0) {
                return markersColors('ideal');
            }
            return markersColors(generatedTeamsRaw[i - 1]['matching_strategy']);
        })
        .attr('stroke-width', 1.5)
        .attr('fill', 'none')
        .attr('x', (_, i) => x(labels[i]))
        .attr('y', yLeft(100))
        .attr('height', yLeft(0) - yLeft(100))
        .attr('width', x.bandwidth());
    // Add text in middle of bars
    svg.append('g')
        .selectAll('g')
        // Enter the stack data = loop key per key = group per group
        .data(allCognitiveModes)
        .enter()
        .append('text')
        .text(d => strings[d])
        .attr('text-anchor', 'middle')
        .attr('dominant-baseline', 'middle')
        .attr('alignment-baseline', 'middle')
        .attr('x', x(labels[0]) + x.bandwidth() / 2)
        .attr('y', (_, i) => (yLeft(stackedData[i][0][0]) + yLeft(stackedData[i][0][1])) / 2)
        .attr('font-weight', 'bold')
        .attr('font-size', `${Math.min(x.bandwidth() / 2, 35)}px`)
        .attr('fill', 'white');
    svg.append('g')
        .selectAll('rect')
        // Enter the stack data = loop key per key = group per group
        .data(generatedTeams.map((data, index) => ({data, index})))
        .enter()
        .filter(({data}, i) => {
            return i > 0 && Object.values(data).every(v => v === 0);
        })
        .append('text')
        .text(strings['nocognitivemodedata'])
        .attr('transform', 'rotate(90)')
        .attr('text-anchor', 'middle')
        .attr('dominant-baseline', 'middle')
        // .attr('alignment-baseline', 'middle')
        .attr('x', ({index}) => x(labels[index]) + x.bandwidth() / 2)
        .attr('y', (yLeft(0) + yLeft(100)) / 2)
        .attr('font-size', 'small')
        .attr('fill', 'black')
        .style("opacity", 0.5)
        .each(function () {
            const bbox = this.getBBox();
            const cx = bbox.x + bbox.width / 2;
            const cy = bbox.y + bbox.height / 2;
            d3.select(this)
                .attr("transform", "rotate(-90," + cx + "," + cy + ")");
        });

    // Add markers
    svg.append('g')
        .selectAll('dot')
        // Enter the stack data = loop key per key = group per group
        .data(cognitiveVariances.map((d, i) => ({"index": i, "data": d})))
        .enter()
        .filter((_, i) => i === 0 || generatedTeamsRaw[i - 1]['matching_strategy'] !== 'randommatchingwithnocognitivemode')
        .append('circle')
        .attr('cx', (d) => x(labels[d['index']]) + x.bandwidth() / 2)
        .attr('cy', d => yRight(d["data"]))
        .attr('r', Math.min(5, x.bandwidth() * 0.45))
        .style('fill', 'black');

};
