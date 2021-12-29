import React from "react";
import YoutubeIcon from "../assets/images/icon-youtube.svg";
export default function VideoCard(props) {
    const containerStyle = {
        position: "relative",
        width: "100%",
        paddingTop: "56.25%"
    };
    const textStyle = {
        position: "absolute",
        top: 0,
        left: 0,
        bottom: 0,
        right: 0,
        // backgroundColor: "#ECF0F3",
        borderRadius: 10
    };
    const buttonStyle = {
        position: "absolute",
        top: "50%",
        left: "50%",
        transform: "translate(-50%, -50%)",
        zIndex: 2,
        border: "none",
        cursor: "pointer",
        outline: "none",
        background: "transparent"
    };
    return (
        <div style={containerStyle}>
            <button style={buttonStyle}>
                <img src={YoutubeIcon} />
            </button>
            <div style={textStyle}>
                <video className="w-100" style={{objectFit:'cover'}} controls>
                    <source src={props.src} />
                </video>
            </div>
        </div>
    );
}
