import{_ as p,m as f,b,c as n,a as s,i as g,g as v,h as i,v as a,j as h,t as d,f as m,e as w,w as y,d as _,o as u}from"./main.js";const k={name:"Login",data(){return{form:{email:"",password:"",remember:!1}}},computed:{...b("auth",["loading","error"])},methods:{...f("auth",["login"]),async handleSubmit(){try{if((await this.login(this.form)).success){const e=this.$route.query.redirect||"/";this.$router.push(e)}}catch(o){console.error("Login error:",o)}}}},V={class:"container mt-4"},S={class:"row justify-content-center"},x={class:"col-md-6"},L={class:"card"},C={class:"card-body"},N={class:"mb-3"},q={class:"mb-3"},B={class:"mb-3 form-check"},D=["disabled"],M={key:0,class:"alert alert-danger mt-3"},U={class:"mt-3 text-center"};function j(o,e,E,R,t,l){const c=_("router-link");return u(),n("div",V,[s("div",S,[s("div",x,[s("div",L,[e[9]||(e[9]=s("div",{class:"card-header"},"Login",-1)),s("div",C,[s("form",{onSubmit:e[3]||(e[3]=g((...r)=>l.handleSubmit&&l.handleSubmit(...r),["prevent"]))},[s("div",N,[e[4]||(e[4]=s("label",{for:"email",class:"form-label"},"Email",-1)),i(s("input",{type:"email",id:"email","onUpdate:modelValue":e[0]||(e[0]=r=>t.form.email=r),class:"form-control",required:""},null,512),[[a,t.form.email]])]),s("div",q,[e[5]||(e[5]=s("label",{for:"password",class:"form-label"},"Password",-1)),i(s("input",{type:"password",id:"password","onUpdate:modelValue":e[1]||(e[1]=r=>t.form.password=r),class:"form-control",required:""},null,512),[[a,t.form.password]])]),s("div",B,[i(s("input",{type:"checkbox",id:"remember","onUpdate:modelValue":e[2]||(e[2]=r=>t.form.remember=r),class:"form-check-input"},null,512),[[h,t.form.remember]]),e[6]||(e[6]=s("label",{for:"remember",class:"form-check-label"}," Remember me ",-1))]),s("button",{type:"submit",class:"btn btn-primary w-100",disabled:o.loading},d(o.loading?"Logging in...":"Login"),9,D),o.error?(u(),n("div",M,d(o.error),1)):v("",!0)],32),s("div",U,[s("p",null,[e[8]||(e[8]=m("Don't have an account? ")),w(c,{to:"/auth/register"},{default:y(()=>e[7]||(e[7]=[m("Register")])),_:1})])])])])])])])}const A=p(k,[["render",j]]);export{A as default};
