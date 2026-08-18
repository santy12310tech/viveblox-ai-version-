import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.180.0/build/three.module.js';

const root=document.querySelector('#game');
const scene=new THREE.Scene(); scene.background=new THREE.Color(0x9ed8ff); scene.fog=new THREE.Fog(0x9ed8ff,35,100);
const camera=new THREE.PerspectiveCamera(65,innerWidth/innerHeight,.1,200);
const renderer=new THREE.WebGLRenderer({antialias:true}); renderer.setPixelRatio(Math.min(devicePixelRatio,2)); renderer.setSize(innerWidth,innerHeight); root.appendChild(renderer.domElement);
scene.add(new THREE.HemisphereLight(0xffffff,0x657080,2));
const sun=new THREE.DirectionalLight(0xffffff,2.2); sun.position.set(15,30,10); scene.add(sun);

const ground=new THREE.Mesh(new THREE.BoxGeometry(60,1,60),new THREE.MeshStandardMaterial({color:0x77b255})); ground.position.y=-.5; scene.add(ground);
for(let x=-24;x<=24;x+=6) for(let z=-24;z<=24;z+=6){ if(Math.abs(x)<7&&Math.abs(z)<7) continue; const h=1+Math.floor(Math.random()*3); const b=new THREE.Mesh(new THREE.BoxGeometry(5,h,5),new THREE.MeshStandardMaterial({color:0xb5b7bb})); b.position.set(x,h/2,z); scene.add(b); }

const player=new THREE.Group();
const body=new THREE.Mesh(new THREE.BoxGeometry(1,1.4,.65),new THREE.MeshStandardMaterial({color:0x3b82f6})); body.position.y=1.1; player.add(body);
const head=new THREE.Mesh(new THREE.BoxGeometry(.85,.85,.85),new THREE.MeshStandardMaterial({color:0xf1c27d})); head.position.y=2.2; player.add(head);
scene.add(player); player.position.set(0,0,4);

const keys={}; addEventListener('keydown',e=>keys[e.code]=true); addEventListener('keyup',e=>keys[e.code]=false);
let yaw=0,pitch=.25,vy=0,onGround=true;
let dragging=false,lastX=0,lastY=0;
renderer.domElement.addEventListener('pointerdown',e=>{dragging=true;lastX=e.clientX;lastY=e.clientY});
addEventListener('pointerup',()=>dragging=false);
addEventListener('pointermove',e=>{if(!dragging)return; yaw-=(e.clientX-lastX)*.006; pitch=Math.max(-.5,Math.min(.7,pitch-(e.clientY-lastY)*.004)); lastX=e.clientX;lastY=e.clientY});

const joy=document.querySelector('#joy'),stick=document.querySelector('#stick'); let joyX=0,joyY=0,joyActive=false;
function joyMove(e){const r=joy.getBoundingClientRect(),cx=r.left+r.width/2,cy=r.top+r.height/2;let dx=e.clientX-cx,dy=e.clientY-cy;const m=45,d=Math.hypot(dx,dy);if(d>m){dx*=m/d;dy*=m/d}joyX=dx/m;joyY=dy/m;stick.style.transform=`translate(${dx}px,${dy}px)`}
joy.addEventListener('pointerdown',e=>{joyActive=true;joy.setPointerCapture(e.pointerId);joyMove(e)}); joy.addEventListener('pointermove',e=>{if(joyActive)joyMove(e)}); joy.addEventListener('pointerup',()=>{joyActive=false;joyX=joyY=0;stick.style.transform='translate(0,0)'});
document.querySelector('#jump').onclick=()=>{if(onGround){vy=7;onGround=false}};

const clock=new THREE.Clock();
function loop(){requestAnimationFrame(loop);const dt=Math.min(clock.getDelta(),.05);let f=(keys.KeyW||keys.ArrowUp?1:0)-(keys.KeyS||keys.ArrowDown?1:0)+(-joyY);let s=(keys.KeyD||keys.ArrowRight?1:0)-(keys.KeyA||keys.ArrowLeft?1:0)+joyX;const len=Math.hypot(f,s)||1;f/=len;s/=len;const speed=7;const forward=new THREE.Vector3(Math.sin(yaw),0,Math.cos(yaw));const right=new THREE.Vector3(Math.cos(yaw),0,-Math.sin(yaw));player.position.addScaledVector(forward,f*speed*dt);player.position.addScaledVector(right,s*speed*dt);player.position.x=Math.max(-27,Math.min(27,player.position.x));player.position.z=Math.max(-27,Math.min(27,player.position.z));if((keys.Space||keys.KeyE)&&onGround){vy=7;onGround=false}vy-=18*dt;player.position.y+=vy*dt;if(player.position.y<=0){player.position.y=0;vy=0;onGround=true}const target=player.position.clone().add(new THREE.Vector3(0,1.2,0));const dist=7;camera.position.set(target.x-Math.sin(yaw)*Math.cos(pitch)*dist,target.y+Math.sin(pitch)*dist,target.z-Math.cos(yaw)*Math.cos(pitch)*dist);camera.lookAt(target);renderer.render(scene,camera)} loop();
addEventListener('resize',()=>{camera.aspect=innerWidth/innerHeight;camera.updateProjectionMatrix();renderer.setSize(innerWidth,innerHeight)});