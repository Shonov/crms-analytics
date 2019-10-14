import Vue from 'vue';
import HelloWorld from '@/views/HelloWorld';

describe('HelloWorld.vue', () => {
  const Constructor = Vue.extend(HelloWorld);
  const vm = new Constructor().$mount();
  it('should render correct contents', () => {
    expect(vm.$el.querySelector('.hello h1').textContent)
      .to.equal('Welcome to Your Vue.js App');
  });
  it('should generate bem classes', () => {
    expect(vm.$el.querySelector('.hello__title').textContent)
      .to.equal('Welcome to Your Vue.js App');
  });
});
