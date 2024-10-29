import React from 'react';
import './../../css/loading.css'
import loading_animation from './../../../public/img/video/loading_animation.gif'

const LoadingAnimation = () => {
  return (
    <div className="spinner-container">
        <img src={loading_animation} alt="Loading animation"/>
      {/* <div className="spinner">
      </div> */}
    </div>
  );
};

export default LoadingAnimation;
