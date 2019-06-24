<template>
  <div class="container">
    <ol class="progressbar">
      <li v-for="i in 4" :key="i" :class="{active: i === 2 }">{{ i }}</li>
    </ol>
  </div>
</template>

<style lang="scss">
  .container {
    width: 100%
  }

  .progressbar {
    counter-reset: step;
    display: flex;
    justify-content: space-around;

    li {
      list-style-type: none;
      position: relative;
      text-align: center;
      width: 100%;

      &:before {
        content: counter(step);
        counter-increment: step;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border: 1px solid #ddd;
        display: block;
        text-align: center;
        margin: 0 auto 10px auto;
        border-radius: 50%;
        background-color: #fff;
      }
      &:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 1px;
        background-color: #ddd;
        top: 15px;
        left: -50%;
        z-index: -1;
      }
      &:first-child {
        content: none;
        &:after {
          content: none;
        }
      }

      &.active {
        color: blue;
        &:before {
          border-color: blue;
        }

        & + li:after {
          background-color: blue;
        }
      }
    }
  }
</style>
