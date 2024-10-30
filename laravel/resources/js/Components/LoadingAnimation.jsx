import React from 'react';
import './../../css/loading.css'
import loading_animation from './../../../public/img/video/LoadingAnimation(no-watermark).svg'

const LoadingAnimation = () => {
  return (
    <div className="loading-container">
      <div className="">
        <img src={loading_animation} alt="Loading animation"/>
      </div>
    </div>
  );
};

export default LoadingAnimation;
