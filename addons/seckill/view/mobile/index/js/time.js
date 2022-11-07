WST.countDown = function(opts){
	var itvTime = (opts.countDownType==1)?100:1000;
	var f = {
		zero: function(n){
			var n = parseInt(n, 10);
			if(n > 0){
				if(n <= 9){
					n = "0" + n;	
				}
				return String(n);
			}else{
				return "0";	
			}
		},
		count: function(){
			if(opts.nowTime){
				var d = new Date();
				d.setTime(opts.nowTime.getTime()+itvTime);
				opts.nowTime = d;
				d = null;
			}else{
				opts.nowTime = new Date();
			}
			//现在将来秒差值
			var dur = 0;
			var pms = {
				msec: "0",
				sec: "0",
				mini: "0",
				hour: "0",
				day: "0"
			};
			var dur = Math.round((opts.endTime.getTime() - opts.nowTime.getTime()));
			if(dur >= 0){
				pms.msec = Math.floor(dur / 100 % 10);
				pms.sec = Math.floor((dur /1000 % 60)) > 0? f.zero(dur / 1000 % 60) : "00";
				pms.mini = Math.floor((dur / 60000)) > 0? f.zero(Math.floor((dur / 60000)) % 60) : "00";
				pms.hour = Math.floor((dur / 3600000)) > 0? f.zero(Math.floor((dur / 3600000)) % 24) : "00";
				pms.day = Math.floor((dur / 86400000)) > 0? f.zero(Math.floor(dur / 86400000)) : "00";
			}
			pms.last = dur;
			pms.nowTime = opts.nowTime;
			opts.callback(pms);
			if(pms.last<=0)clearInterval(itv);
		}
	};
	var itv = setInterval(f.count, itvTime);
	return itv;
};