import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';
import Edit from './edit';
import './style.css';

function save({ attributes }) {
  const { effect, autoplay, autoplayDelay, loop, showIndicators } = attributes;

  const swiperConfig = JSON.stringify({
    effect,
    loop,
    speed: 800,
    autoplay: autoplay ? { delay: autoplayDelay, disableOnInteraction: false } : false,
    pagination: showIndicators ? { el: '.swiper-pagination', clickable: true } : false,
    fadeEffect: { crossFade: true },
  });

  const blockProps = useBlockProps.save({
    className: 'swiper mytheme-carousel',
    'data-swiper': swiperConfig,
  });

  const innerBlocksProps = useInnerBlocksProps.save({
    className: 'swiper-wrapper',
  });

  return (
    <div {...blockProps}>
      <div {...innerBlocksProps} />
      {showIndicators && <div className="swiper-pagination" />}
    </div>
  );
}

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save,
});