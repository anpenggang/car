/*!
** by zhangxinxu(.com)
** ��HTML5 video��Ƶ��ʵ�����ĵ�ĻЧ��
** http://www.zhangxinxu.com/wordpress/?p=6386
** MIT License
** ������Ȩ����
*/

function getVideoInfo () {
    var video = $('#videoBarrage');
    var videoH = video[0].videoHeight;
    var videoW = video[0].videoWidth;
    console.log("Height: " + video[0].videoHeight + ", Width: " + video[0].videoWidth);
    console.log("Height: " + videoH + ", Width: " + videoW);
    var videoRatio = videoH / videoW;
    console.log(videoRatio);
    window.onresize = function() {
        if (video.height() / video.width() > videoRatio) {
            console.log('Width:' + video.width() + ' Height: ' + (video.width() * videoRatio));
        } else {
            console.log('Width:' + video.height() / videoRatio + ' Height: ' + video.height());
        }
    }
}
var tScale  = window.devicePixelRatio,
      tWidth  = 400,
      tHeight = 300;
      getVideoInfo();
var CanvasBarrage = function (canvas, video, options) {
	  var tScale  = window.devicePixelRatio,
      tWidth  = 400,
      tHeight = 300;
      tWidth=375;
      tHeight=212;
        if (!canvas || !video) {
		return;	
	}
	var defaults = {
		opacity: 100,
		fontSize: 24,
		speed: 2,
		range: [0,1],
		color: 'white',
		data: []
	};
	
	options = options || {};
	
	var params = {};
	// �����ϲ�
	for (var key in defaults) {
		if (options[key]) {
			params[key] = options[key];
		} else {
			params[key] = defaults[key];
		}
		
		this[key] = params[key];
	}
	var top = this;
	var data = top.data;
	
	if (!data || !data.length) {
		return;
	}

	var context = canvas.getContext('2d');
	canvas.width = canvas.clientWidth*tScale;
	canvas.height = canvas.clientHeight*tScale;

	// �洢ʵ��
	var store = {};
	
	// ��ͣ���
	var isPause = true;
	// ����ʱ��
	var time = video.currentTime;

	// �ֺŴ�С
	var fontSize = 24*tScale;

	// ʵ������
	var Barrage = function (obj) {
		// һЩ��������
		this.value = obj.value;
		this.time = obj.time;
		// data�еĿ��Ը���ȫ�ֵ�����
		this.init = function () {
			// 1. �ٶ�
			var speed = top.speed;
		//	console.log("speed"+speed);
			if (obj.hasOwnProperty('speed')) {
				speed = obj.speed;
			}
		//	console.log("length"+obj.value.length);
			if (speed !== 0) {
				// ����������ͬ���ٶȻ���΢��
				speed = speed + obj.value.length / 100;
			}
			// 2. �ֺŴ�С
			var fontSize = obj.fontSize || top.fontSize;

			
			
			
			
			// 3. ������ɫ
			var color = obj.color || top.color;
			// ת����rgb��ɫ
			color = (function () {
				var div = document.createElement('div');
				div.style.backgroundColor = color;
				document.body.appendChild(div);
				var c = window.getComputedStyle(div).backgroundColor;	
				document.body.removeChild(div);
				return c;
			})();
			
			// 4. range��Χ
			var range = obj.range || top.range;
			// 5. ͸����
			var opacity = obj.opacity || top.opacity;
			opacity = opacity / 100;
		        console.log("opacity"+opacity);	
			// ��������ݳ���
			var span = document.createElement('span');
			span.style.position = 'absolute';
			span.style.whiteSpace = 'nowrap';
			span.style.font = 'bold ' + fontSize*tScale + 'px "microsoft yahei", sans-serif';
			span.innerText = obj.value;
			span.textContent = obj.value;
			document.body.appendChild(span);
			// ����������ݿ��
			this.width = span.clientWidth;
			// �Ƴ�domԪ��
			document.body.removeChild(span);
			
			// ��ʼˮƽλ�úʹ�ֱλ��
			this.x = canvas.width;
			if (speed == 0) {
				this.x	= (this.x - this.width) / 2;
			}
			this.actualX = canvas.width;
                        console.log("height"+(range[1]-range[0])*(canvas.height-20*tScale));                       
                        this.y = range[0] * canvas.height + (range[1] - range[0])* (canvas.height-20*tScale) * Math.random();
                        //console.log("Math.random"+Math.random());
                        //console.log("this.y"+this.y);
			if (this.y < fontSize) {
				this.y = fontSize;
			} else if (this.y > canvas.height - fontSize) {
				this.y = canvas.height - fontSize*tScale;
			}
			
			this.moveX = speed;
			this.opacity = opacity;
			this.color = color;
			this.range = range;
			this.fontSize = fontSize;	
		};
		
		this.draw = function () {			
			// ���ݴ�ʱxλ�û����ı�
			context.shadowColor = 'rgba(0,0,0,'+ this.opacity +')';
			context.shadowOffsetX=1;
            context.shadowOffsetY=0;
            context.shadowBlur =2;
			context.font = this.fontSize + 'px "microsoft yahei", sans-serif';
			if (/rgb\(/.test(this.color)) {
				context.fillStyle = 'rgba('+ this.color.split('(')[1].split(')')[0] +','+ this.opacity +')';
			} else {
				context.fillStyle = this.color;	
			}
			// ��ɫ
			context.fillText(this.value, this.x*tScale, this.y*tScale);
			//console.log(this.x);
			//console.log(this.y);
		};
	};

	data.forEach(function (obj, index) {
		store[index] = new Barrage(obj);
	});

	// ���Ƶ�Ļ�ı�
	var draw = function () {
		for (var index in store) {
			var barrage = store[index];
			//console.log(store);
			//console.log(store[1]);
			if (barrage && !barrage.disabled && time >= barrage.time) {
				if (!barrage.inited) {
					barrage.init();
					barrage.inited = true;
				}
				barrage.x -= barrage.moveX;
				if (barrage.moveX == 0) {
					// �����ĵ�Ļ
					barrage.actualX -= top.speed;
				} else {
					barrage.actualX = barrage.x;
				}
				// �Ƴ���Ļ
				if (barrage.actualX < -1 * barrage.width) {
					// �������и�speedΪ0�ĵ�Ļ
					barrage.x = barrage.actualX;
					// �õ�Ļ���˶�
					barrage.disabled = true;
				}
				// ������λ�û���ԲȦȦ
				barrage.draw();	
			}
		}
	};
	
	// ������Ⱦ
	var render = function () {
		// �����Ѿ�����ʱ��
		time = video.currentTime;
		// �������
		context.clearRect(0, 0, canvas.width, canvas.height);
		
		// ���ƻ���
		draw();

		// ������Ⱦ
		if (isPause == false) {
			requestAnimationFrame(render);
		}
	};
	
	// ��Ƶ����
	video.addEventListener('play', function () {
		isPause = false;
		render();
	});
	video.addEventListener('pause', function () {
		isPause = true;
	});
	video.addEventListener('seeked', function () {
		// ��ת������Ҫ����
		top.reset();
	});
	
	
	// ������ݵķ��� 
	this.add = function (obj) {
		store[Object.keys(store).length] = new Barrage(obj);
	};
	
	// ����
	this.reset = function () {
		time = video.currentTime;
		// �������
		context.clearRect(0, 0, canvas.width, canvas.height);
		
		for (var index in store) {
			var barrage = store[index];
			if (barrage) {
				// ״̬�仯
				barrage.disabled = false;
				// ����ʱ���ж���Щ��������
				if (time < barrage.time) {
					// ��Ƶʱ��С�ڲ���ʱ��
					// barrage.disabled = true;
					barrage.inited = null;
				} else {
					// ��Ƶʱ����ڲ���ʱ��
					barrage.disabled = true;
				}
			}
		}
	};
};
