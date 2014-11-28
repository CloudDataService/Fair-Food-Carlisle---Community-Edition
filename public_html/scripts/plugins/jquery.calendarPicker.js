jQuery.fn.calendarPicker = function(options) {
    //from http://bugsvoice.com/applications/bugsVoice/site/test/calendarPickerDemo.jsp
  // --------------------------  start default option values --------------------------
  if (!options.date) {
    options.date = window.today; //new Date();
  }

  if (typeof(options.years) == "undefined" && options.date.getMonth() > 9)
  {
	options.years=1;
  }
  else if (typeof(options.years) == "undefined")
  {
	options.years=0;
  }

  if (typeof(options.months) == "undefined")
    options.months=3;

  if (typeof(options.days) == "undefined")
    options.days=4;

  if (typeof(options.showDayArrows) == "undefined")
    options.showDayArrows=true;

  if (typeof(options.useWheel) == "undefined")
    options.useWheel=true;

  if (typeof(options.callbackDelay) == "undefined")
    options.callbackDelay=500;
  
  if (typeof(options.monthNames) == "undefined")
    options.monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

  if (typeof(options.dayNames) == "undefined")
    options.dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];


  // --------------------------  end default option values --------------------------

  var calendar = {currentDate: options.date};
  calendar.options = options;

  //build the calendar on the first element in the set of matched elements.
  var theDiv = this.eq(0);//$(this);
  theDiv.addClass("calBox");

  //empty the div
  theDiv.empty();

    //key dates in our calendar
    calendar.datesOrdered = new Object();
    if (typeof(options.datesAvailable) == "undefined")
        options.datesAvailable = new Object();

  var divYears = $("<div>").addClass("calYear");
  var divMonths = $("<div>").addClass("calMonth");
  var divDays = $("<div>").addClass("calDay");


  theDiv.append(divYears).append(divMonths).append(divDays);

  calendar.changeDate = function(date) {
    calendar.currentDate = date;

    var fillYears = function(date) {
      var year = date.getFullYear();
      var t = window.today; //new Date();
      divYears.empty();
      divYears.html('<span class="calLabel">Year</span>');
      var nc = options.years*2+1;
      var w = parseInt((theDiv.width()-4-(nc)*4)/nc)+"px";
      for (var i = t.getFullYear(); i <= t.getFullYear() + options.years; i++) {
        var d = new Date(date);
        d.setFullYear(i);
        if(date.getFullYear() == t.getFullYear()) {
            d.setMonth(0); //when current year clicked, display Dec
        } else {
            d.setMonth(11); //when next year clicked, month will be Jan
        }
        var span = $("<span>").addClass("calElement").attr("millis", d.getTime()).html(i); //.css("width",w);
        if (d.getYear() == t.getYear())
          span.addClass("today");
        if (d.getYear() == calendar.currentDate.getYear())
          span.addClass("selected");
        divYears.append(span);
      }
    }
    
    function lastDayOfMonth(y,m,dy) {
        var  days = {sun:0,mon:1,tue:2,wed:3,thu:4,fri:5,sat:6}
         ,dat = new Date(y+'/'+m+'/1')
         ,currentmonth = m
         ,firstday = false;
        while (currentmonth === m){
            firstday = dat.getDay() === days[dy] || firstday;
            dat.setDate(dat.getDate()+(firstday ? 7 : 1));
            currentmonth = dat.getMonth()+1 ;
        }
        dat.setDate(dat.getDate()-7);
        return dat;
    }

    var fillMonths = function(date) {
      var month = date.getMonth();
      var t = window.today; //new Date();
      divMonths.empty();
      divMonths.html('<span class="calLabel">Month</span>');
      var oldday = date.getDay();
      var nc = options.months*2+1;
      var w = parseInt((theDiv.width()-4-(nc)*4)/nc + 35)+"px";
      for (var i = 0; i <= 11; i++) {
        var d = new Date(date);
        var oldday = d.getDate();
		d.setDate(1); //otherwise if today is the 31st, then months with less dates will instead jump forward & duplicate another month;
        d.setMonth(i);
		
		//work out last Tuesday of month...
		var lastDay = lastDayOfMonth(t.getYear(), t.getMonth(), 'tue');
		//console.log(d + 'current month? ' + (t.getMonth() != d.getMonth()) + ',  last week? ' + (t.getDate() < lastDay ) + ', today: '+ t.getDate() + ', last date: '+ lastDay );
       // console.log('This i: '+ i +', this month: '+ d.getMonth());
		
        //do we want to display this month?...
        if( (
				((d.getYear() == t.getYear()) && d.getMonth() >= t.getMonth())	//not in the past this year
        		||  (d.getYear() > t.getYear())	//is a past year
			)
			&& (t.getMonth() != d.getMonth() || d.getDate() <= lastDay.getDate() ) //if its the current month, it isn't the last week
        ) { 
            /*
            if (d.getDate() != oldday) {
              d.setMonth(d.getMonth() - 1);
              d.setDate(28);
            }
            */
            var span = $("<span>").addClass("calElement").attr("millis", d.getTime()).html(options.monthNames[d.getMonth()]).css("width",w);
            if (d.getYear() == t.getYear() && d.getMonth() == t.getMonth())
            {
              span.addClass("today");
            }
            if (d.getYear() == calendar.currentDate.getYear() && d.getMonth() == calendar.currentDate.getMonth())
            {
              span.addClass("selected");
            }
            divMonths.append(span);
        }

      }
    }

    var fillDays = function(date) {
      var day = date.getDate();
      var t = window.today; //new Date();
      divDays.empty();
      divDays.html('<span class="calLabel">Day</span>');
      var nc = options.days*2+1;
      //var w = parseInt((theDiv.width()-4-(options.showDayArrows?12:0)-(nc)*4)/(nc-(options.showDayArrows?2:0)) +50)+"px";
      var w = 50;
      for (var i = 0; i <= 31; i++) {
        var d = new Date(date);
        d.setDate(i);
        if((calendar.currentDate.getMonth() == d.getMonth()) //if its the selected month
        && (d.getDay() == 2)	//and it's Tuesday
        && (d >= t) ) {	//and it's not in the past
            var span = $("<span>").addClass("calElement").addClass("dayBox").attr("millis", d.getTime());
            if ((options.datesAvailable[ d.getTime()] != null) || options.datesAvailable[ d.getTime() + 3600000] != null) { //its available for ordering now (or an hour later)
                span.addClass("calSelectable");
            }
            if (calendar.datesOrdered[ d.getTime()] != null) {
                span.addClass("dayOrdering");
            }
            if (i == -options.days && options.showDayArrows) {
              span.addClass("prev");
            } else if (i == options.days && options.showDayArrows) {
              span.addClass("next");
            } else {
              span.html(options.dayNames[d.getDay()] +'<br><span class="dayNumber">' + d.getDate() + "</span>" ).css("width",w);
              if (d.getYear() == t.getYear() && d.getMonth() == t.getMonth() && d.getDate() == t.getDate())
                span.addClass("today");
              if (d.getYear() == calendar.currentDate.getYear() && d.getMonth() == calendar.currentDate.getMonth() && d.getDate() == calendar.currentDate.getDate())
                span.addClass("selected");
            }
            divDays.append(span);
        }
      }
    }

    var deferredCallBack = function() {
      if (typeof(options.callback) == "function") {
        if (calendar.timer)
          clearTimeout(calendar.timer);

        calendar.timer = setTimeout(function() {
          //options.callback(calendar); //run the function given in options
        }, options.callbackDelay);
      }
    }

    //all days should be at midnight
    date.setHours(0);
    date.setMinutes(0);
    date.setSeconds(0);
    date.setMilliseconds(0);
    
    //put in the cal objects
    fillYears(date);
    fillMonths(date);
    fillDays(date);

    deferredCallBack();

  }

  theDiv.click(function(ev) {
    var el = $(ev.target).closest(".calElement");
    if (el.hasClass("calSelectable")) {
        //a selectable day was clicked, order it?...
        calendar.dayClicked =  new Date(parseInt(el.attr("millis")));
        calendar.millisClicked = el.attr("millis");
        options.callback(calendar); //run the function given in options
        //console.log(calendar.dayClicked.getDate());
    } else {
        //not a day clicked, flip the calendar?...
        calendar.dayClicked = null;
        if (el.hasClass("calElement")) {
            if (el.hasClass("dayBox")) {
                //day not availble!
                $('#commitment_notes').html('The product is not available on that day.');
                $('#commitment_notes').fadeIn();
            }
            else {
            //console.log('change to: ' + new Date(parseInt(el.attr("millis"))));
                calendar.changeDate(new Date(parseInt(el.attr("millis"))));
            }
        }
    }
  });


  //if mousewheel
  if ($.event.special.mousewheel && options.useWheel) {
    divYears.mousewheel(function(event, delta) {
      var d = new Date(calendar.currentDate.getTime());
      d.setFullYear(d.getFullYear() + delta);
      calendar.changeDate(d);
      return false;
    });
    divMonths.mousewheel(function(event, delta) {
      var d = new Date(calendar.currentDate.getTime());
      d.setMonth(d.getMonth() + delta);
      calendar.changeDate(d);
      return false;
    });
    divDays.mousewheel(function(event, delta) {
      var d = new Date(calendar.currentDate.getTime());
      d.setDate(d.getDate() + delta);
      calendar.changeDate(d);
      return false;
    });
  }

  calendar.changeDate(options.date);

  return calendar;
};