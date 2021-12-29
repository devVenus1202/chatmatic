import React from 'react';

import '../style.scss';

const FollowButtons = ({ followInfo, isAdmin, onFollow, isFollowed }) => {
  const followerCount = !!followInfo && !!followInfo.followers && followInfo.followers.length;
  const followingCount = !!followInfo && !!followInfo.followings && followInfo.followings.length;
  const handleFollow = () => {
    onFollow();
  }

  return (
    <div className="follow-section">
      <button className="followers">
        <span className="number">{followerCount}</span>
        <span>followers</span>
      </button>
      <button className="following">
        <span className="number">{followingCount}</span>
        <span>followings</span>
      </button>
      {!isAdmin ? <button className="follow-button" onClick={handleFollow} disabled={isFollowed}>+Follow</button> : null}
    </div>
  );
};

export default FollowButtons;