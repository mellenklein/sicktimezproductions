$(document).ready(function() {
  var switched = false;
  var updateTables = function() {
    if (($(window).width() < 767) && !switched ){
      switched = true;
      $("table.responsive").each(function(i, element) {
        splitTable($(element));
      });
      return true;
    }
    else if (switched && ($(window).width() > 767)) {
      switched = false;
      $("table.responsive").each(function(i, element) {
        unsplitTable($(element));
      });
    }
  };

  $(window).load(updateTables);
  $(window).on("redraw",function(){switched=false;updateTables();}); // An event to listen for
  $(window).on("resize", updateTables);
	$(window).on("resize", splitTable);


	function splitTable(original)
	{
		original.wrap("<div class='table-wrapper' />");

		var copy = original.clone();
		if(copy.find("td:first-child").hasClass('hide-for-mobile')) {
			copy.find("td:not(:nth-child(2)), th:not(:nth-child(2))").css("display", "none");
			console.log(original);
			original.find("td:first-child, th:first-child, td:nth-child(2), th:nth-child(2)").css("display", "none");
		} else {
			copy.find("td:not(:first-child), th:not(:first-child)").css("display", "none");
		}
		copy.removeClass("responsive");

		original.closest(".table-wrapper").append(copy);
		copy.wrap("<div class='pinned' />");
		original.wrap("<div class='scrollable' />");

    setCellHeights(original, copy);
	}

	function unsplitTable(original) {
    original.closest(".table-wrapper").find(".pinned").remove();
    original.unwrap();
    original.unwrap();
		if(original.clone().find("td:first-child").hasClass('hide-for-mobile')) {
			original.clone().find("td:not(:nth-child(2)), th:not(:nth-child(2))").css("display", "table-cell");
			console.log(original);
			original.find("td:first-child, th:first-child, td:nth-child(2), th:nth-child(2)").css("display", "table-cell");
		}
	}

  function setCellHeights(original, copy) {
    var tr = original.find('tr'), // find all the tr rows wrapped inside the 'scrollable' div
        tr_copy = copy.find('tr'), // find all the tr rows wrapped inside the 'pinned' div
        heights = [];

    tr.each(function (index) {  // inside scrollable, one row at a time, search the row for th and td elements and store those elements in 'tx'
      var self = $(this),
          tx = self.find('th, td');

      tx.each(function () { // each time a th or td is found nested inside a row, find the height of the first matched element - including margin
        var height = $(this).outerHeight(true);
        heights[index] = heights[index] || 0; // if the height is empty at this index, set the height to 0 for now
        if (height > heights[index]) {  // if the height of the scrollable td or th element is taller than the empty height at this index (0),
					heights[index] = height;  // then adjust the column on the left to match the height of the scrollable col at the same index
				}
      });

    });

    tr_copy.each(function (index) {
			console.log(heights[index]);
      $(this).height(heights[index]);
    });
  }

});
